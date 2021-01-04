<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Installment;
use App\Models\InstallmentItem;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
//5.4-new. 分期付款列表页 新建:
class InstallmentsController extends Controller
{
    public function index(Request $request){
        $installments = Installment::query()
            ->where('user_id', $request->user()->id)
            ->paginate(10);
        return view('installments.index', ['installments' => $installments]);
    }

    //5.5-new. 分期详情页  添加:
    public function show(Installment $installment){
        // 取出当前分期付款的所有的还款计划，并按还款顺序排序
        $items = $installment->items()->orderBy('sequence')->get();
        return view('installments.show', [
            'installment' => $installment,
            'items'       => $items,
            // 下一个未完成还款的还款计划
            'nextItem'    => $items->where('paid_at', null)->first(),
        ]);
    }

    // 5.6-new. 分期还款（支付宝） 添加:
    public function payByAlipay(Installment $installment){
        if ($installment->order->closed) {
            throw new InvalidRequestException('对应的商品订单已被关闭');
        }
        if ($installment->status === Installment::STATUS_FINISHED) {
            throw new InvalidRequestException('该分期订单已结清');
        }
        // 获取当前分期付款最近的一个未支付的还款计划
        if (!$nextItem = $installment->items()->whereNull('paid_at')->orderBy('sequence')->first()) {
            // 如果没有未支付的还款，原则上不可能，因为如果分期已结清则在上一个判断就退出了
            throw new InvalidRequestException('该分期订单已结清');
        }

        // 调用支付宝的网页支付
        return app('alipay')->web([
            // 支付订单号使用分期流水号+还款计划编号
            'out_trade_no' => $installment->no.'_'.$nextItem->sequence,
            'total_amount' => $nextItem->total,
            'subject'      => '支付 Laravel Shop 的分期订单：'.$installment->no,
            // 这里的 notify_url 和 return_url 可以覆盖掉在 AppServiceProvider 设置的回调地址
            'notify_url'   => ngrok_url('installments.alipay.notify'), //5.6. 添加
            'return_url'   => route('installments.alipay.return'), //5.6. 添加
        ]);
    }

    // 5.6-new: 支付宝前端回调
    public function alipayReturn()
    {
        try {
            app('alipay')->verify();
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        return view('pages.success', ['msg' => '付款成功']);
    }

    //5-6-new: 支付宝后端回调
    public function alipayNotify()
    {
        // 校验支付宝回调参数是否正确
        $data = app('alipay')->verify();
        // 拉起支付时使用的支付订单号是由分期流水号 + 还款计划编号组成的
        // 因此可以通过支付订单号来还原出这笔还款是哪个分期付款的哪个还款计划
        list($no, $sequence) = explode('_', $data->out_trade_no);
        // 根据分期流水号查询对应的分期记录，原则上不会找不到，这里的判断只是增强代码健壮性
        if (!$installment = Installment::query()->where('no', $no)->first()) {
            return 'fail';
        }
        // 根据还款计划编号查询对应的还款计划，原则上不会找不到，这里的判断只是增强代码健壮性
        if (!$item = $installment->items()->where('sequence', $sequence)->first()) {
            return 'fail';
        }
        // 如果这个还款计划的支付状态是已支付，则告知支付宝此订单已完成，并不再执行后续逻辑
        if ($item->paid_at) {
            return app('alipay')->success();
        }

        // 更新对应的还款计划
        $item->update([
            'paid_at'        => Carbon::now(), // 支付时间
            'payment_method' => 'alipay', // 支付方式
            'payment_no'     => $data->trade_no, // 支付宝订单号
        ]);

        // 如果这是第一笔还款
        if ($item->sequence === 0) {
            // 将分期付款的状态改为还款中
            $installment->update(['status' => Installment::STATUS_REPAYING]);
            // 将分期付款对应的商品订单状态改为已支付
            $installment->order->update([
                'paid_at'        => Carbon::now(),
                'payment_method' => 'installment', // 支付方式为分期付款
                'payment_no'     => $no, // 支付订单号为分期付款的流水号
            ]);
            // 触发商品订单已支付的事件
            event(new OrderPaid($installment->order));
        }
        // 如果这是最后一笔还款
        if ($item->sequence === $installment->count - 1) {
            // 将分期付款状态改为已结清
            $installment->update(['status' => Installment::STATUS_FINISHED]);
        }
        return app('alipay')->success();
    }

    /**
     * 5.9-new. 退款处理  添加:
     * 由于微信退款的结果需要通过回调通知，因此我们需要创建一个接口来接收通知
     */
    public function wechatRefundNotify(Request $request)
    {
        // 给微信的失败响应
        $failXml = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
        // 校验微信回调参数
        $data = app('wechat_pay')->verify(null, true);
        // 根据单号拆解出对应的商品退款单号及对应的还款计划序号
        list($no, $sequence) = explode('_', $data['out_refund_no']);

        $item = InstallmentItem::query()
            ->whereHas('installment', function ($query) use ($no) {
                $query->whereHas('order', function ($query) use ($no) {
                    $query->where('refund_no', $no); // 根据订单表的退款流水号找到对应还款计划
                });
            })
            ->where('sequence', $sequence)
            ->first();

        // 没有找到对应的订单，原则上不可能发生，保证代码健壮性
        if (!$item) {
            return $failXml;
        }

        // 如果退款成功
        if ($data['refund_status'] === 'SUCCESS') {
            // 将还款计划退款状态改成退款成功
            $item->update([
                'refund_status' => InstallmentItem::REFUND_STATUS_SUCCESS,
            ]);
            // 设定一个标志位
           /* $allSuccess = true;
            foreach ($item->installment->items as $item) {
                if ($item->paid_at && $item->refund_status !== InstallmentItem::REFUND_STATUS_SUCCESS) {
                    $allSuccess = false;
                    break;
                }
            }
            // 如果所有退款都成功，则将对应商品订单的退款状态修改为退款成功
            if ($allSuccess) {
                $item->installment->order->update([
                    'refund_status' => Order::REFUND_STATUS_SUCCESS,
                ]);
            }*/

            $item->installment->refreshRefundStatus(); //5.9-new. 退款处理 修改
        } else {
            // 否则将对应还款计划的退款状态改为退款失败
            $item->update([
                'refund_status' => InstallmentItem::REFUND_STATUS_FAILED,
            ]);
        }

        return app('wechat_pay')->success();
    }
}
