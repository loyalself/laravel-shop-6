<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Notifications\OrderPaidNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
// 7.6. 完善支付后逻辑 新建: 创建监听器来执行发送邮件的动作
//class SendOrderPaidMail
// implements ShouldQueue 代表异步监听器
class SendOrderPaidMail implements ShouldQueue
{
    public function handle(OrderPaid $event){
        // 从事件对象中取出对应的订单
        $order = $event->getOrder();
        // 调用 notify 方法来发送通知
        $order->user->notify(new OrderPaidNotification($order));
    }
}
