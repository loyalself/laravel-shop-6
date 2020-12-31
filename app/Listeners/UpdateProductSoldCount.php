<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\OrderItem;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
//7.6. 完善支付后逻辑 新建: 我们希望订单支付之后对应的商品销量会对应地增加，所以创建一个更新商品销量的监听器.别忘了在 EventServiceProvider 中将事件和监听器关联起来.
//class UpdateProductSoldCount
//  implements ShouldQueue 代表此监听器是异步执行的
class UpdateProductSoldCount implements ShouldQueue
{
    // Laravel 会默认执行监听器的 handle 方法，触发的事件会作为 handle 方法的参数
    public function handle(OrderPaid $event){
        // 从事件对象中取出对应的订单
        $order = $event->getOrder();
        // 循环遍历订单的商品
        foreach ($order->items as $item) {
            $product   = $item->product;
            // 计算对应商品的销量
            $soldCount = OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', function ($query) {
                    $query->whereNotNull('paid_at');  // 关联的订单状态是已支付
                })->sum('amount');
            // 更新商品销量
            $product->update([
                'sold_count' => $soldCount,
            ]);
        }
    }
}
