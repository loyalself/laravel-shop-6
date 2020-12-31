<?php
namespace App\Listeners;
use App\Events\OrderPaid;
use App\Models\Order;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
/**
 * 4.6-new. 订单模块调整 新建:
 *与普通商品订单不同，众筹商品订单在支付成功之后，我们还需要更新对应众筹商品的众筹进度。
  与更新商品销量类似，我们创建一个 OrderPaid 事件的监听器来实现众筹进度的更新
 */
//class UpdateCrowdfundingProductProgress //default
class UpdateCrowdfundingProductProgress implements ShouldQueue
{
    public function __construct(){

    }

    /**
     * 重点看一下 first()方法,first()方法接受一个数组作为参数,代表此次 SQL 要查询出来的字段,默认情况下 Laravel 会给数组里面的值的两边加上 ` 这个符号,比如:
     *  first(['name', 'email']) 生成的 SQL 会类似: select `name`, `email` from xxx.
     *
     * 所以如果我们直接传入 first(['sum(total_amount) as total_amount', 'count(distinct(user_id)) as user_count']),
     * 最后生成的 SQL 肯定是不正确的。这里我们用 DB::raw() 方法来解决这个问题,Laravel 在构建 SQL 的时候如果遇到 DB::raw() 就会把 DB::raw() 的参数原样拼接到 SQL 里.
     *
     */
    public function handle(OrderPaid $event){
        $order = $event->getOrder();
        // 如果订单类型不是众筹商品订单，无需处理
        if ($order->type !== Order::TYPE_CROWDFUNDING) {
            return;
        }
        $crowdfunding = $order->items[0]->product->crowdfunding;

        $data = Order::query()
            // 查出订单类型为众筹订单
            ->where('type', Order::TYPE_CROWDFUNDING)
            // 并且是已支付的
            ->whereNotNull('paid_at')
            ->whereHas('items', function ($query) use ($crowdfunding) {
                // 并且包含了本商品
                $query->where('product_id', $crowdfunding->product_id);
            })
            ->first([
                // 取出订单总金额
                \DB::raw('sum(total_amount) as total_amount'),
                // 取出去重的支持用户数
                \DB::raw('count(distinct(user_id)) as user_count'),
            ]);

        $crowdfunding->update([
            'total_amount' => $data->total_amount,
            'user_count'   => $data->user_count,
        ]);
    }
}
