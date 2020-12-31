<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
//7.3. 订单的支付宝支付 新建
class PaymentController extends Controller
{
    public function payByAlipay(Order $order, Request $request)
    {
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
    public function alipayNotify()
    {
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
}
