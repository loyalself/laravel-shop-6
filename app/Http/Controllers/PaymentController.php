<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Installment;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

//7.3. 订单的支付宝支付 新建
class PaymentController extends Controller
{
    public function payByAlipay(Order $order, Request $request){
        // 判断订单是否属于当前用户
        $this->authorize('own', $order);
        // 订单已支付或者已关闭
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }

        // 调用支付宝的网页支付
        return app('alipay')->web([
            'out_trade_no' => $order->no, // 订单编号，需保证在商户端不重复
            'total_amount' => $order->total_amount, // 订单金额，单位元，支持小数点后两位
            'subject'      => '支付 Laravel Shop 的订单：'.$order->no, // 订单标题
        ]);
    }
    /**
     * 7.3 订单的支付宝支付 添加: 前端回调页面
     * 1.app('alipay')->verify() 用于校验提交的参数是否合法，支付宝的前端跳转会带有数据签名，通过校验数据签名可以判断参数是否被恶意用户篡改。同时该方法还会返回解析后的参数。
       2.dd($data); 输出解析后的数据，我们要先看看会返回什么再决定如何处理
     */
    public function alipayReturn(){
        // 校验提交的参数是否合法
        /*$data = app('alipay')->verify();
        dd($data);*/

        //7.3 同章修改
        try {
            app('alipay')->verify();
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }
        return view('pages.success', ['msg' => '付款成功']);
    }

    // 7.3 添加: 服务器端回调
    public function alipayNotify(){
        /*$data = app('alipay')->verify();
        //\Log::debug('Alipay notify', $data->all()); 由于服务器端的请求我们无法看到返回值，使用 dd 就不行了，所以需要通过日志的方式来保存。
        \Log::debug('Alipay notify', $data->all());*/

        //7.3 同章修改:
        // 校验输入参数
        $data  = app('alipay')->verify();
        // $data->out_trade_no 拿到订单流水号，并在数据库中查询
        $order = Order::query()->where('no', $data->out_trade_no)->first();
        // 正常来说不太可能出现支付了一笔不存在的订单，这个判断只是加强系统健壮性。
        if (!$order) {
            return 'fail';
        }
        // 如果这笔订单的状态已经是已支付
        if ($order->paid_at) {
            $this->afterPaid($order); //7.6
            /**
             * 返回数据给支付宝，支付宝得到这个返回之后就认为我们已经处理好这笔订单，不会再发生这笔订单的回调了。如果我们返回其他数据给支付宝，支付宝就会每隔一段时间就发送一次服务器端回调，直到我们返回了正确的数据为准
             */
            return app('alipay')->success();
        }
        $order->update([
            'paid_at'        => Carbon::now(), // 支付时间
            'payment_method' => 'alipay', // 支付方式
            'payment_no'     => $data->trade_no, // 支付宝订单号
        ]);
        return app('alipay')->success();
    }
    /**
     * 7.6 完善支付后的逻辑
     * @param Order $order
     */
    protected function afterPaid(Order $order){
        event(new OrderPaid($order));
    }
    /**
     * 5.3-new. 创建分期付款  新建:
     */
    public function payByInstallment(Order $order, Request $request){
        // 判断订单是否属于当前用户
        $this->authorize('own', $order);
        // 订单已支付或者已关闭
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }
        // 校验用户提交的还款月数，数值必须是我们配置好费率的期数
        $this->validate($request, [
            'count' => ['required', Rule::in(array_keys(config('app.installment_fee_rate')))],
        ]);
        // 删除同一笔商品订单发起过其他的状态是未支付的分期付款，避免同一笔商品订单有多个分期付款
        Installment::query()
            ->where('order_id', $order->id)
            ->where('status', Installment::STATUS_PENDING)
            ->delete();
        $count = $request->input('count');
        // 创建一个新的分期付款对象
        $installment = new Installment([
            // 总本金即为商品订单总金额
            'total_amount' => $order->total_amount,
            // 分期期数
            'count'        => $count,
            // 从配置文件中读取相应期数的费率
            'fee_rate'     => config('app.installment_fee_rate')[$count],
            // 从配置文件中读取当期逾期费率
            'fine_rate'    => config('app.installment_fine_rate'),
        ]);
        $installment->user()->associate($request->user());
        $installment->order()->associate($order);
        $installment->save();
        // 第一期的还款截止日期为明天凌晨 0 点
        $dueDate = Carbon::tomorrow();
        // 计算每一期的本金
        $base = big_number($order->total_amount)->divide($count)->getValue();
        // 计算每一期的手续费
        $fee = big_number($base)->multiply($installment->fee_rate)->divide(100)->getValue();
        // 根据用户选择的还款期数，创建对应数量的还款计划
        for ($i = 0; $i < $count; $i++) {
            // 最后一期的本金需要用总本金减去前面几期的本金
            if ($i === $count - 1) {
                $base = big_number($order->total_amount)->subtract(big_number($base)->multiply($count - 1));
            }
            $installment->items()->create([
                'sequence' => $i,
                'base'     => $base,
                'fee'      => $fee,
                'due_date' => $dueDate,
            ]);
            // 还款截止日期加 30 天
            $dueDate = $dueDate->copy()->addDays(30);
        }
        return $installment;
    }
}
