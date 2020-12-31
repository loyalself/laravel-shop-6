<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;
use App\Jobs\CloseOrder;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Services\CartService;
use Carbon\Carbon;
use Illuminate\Http\Request;
//6.4 购物车下单页面  新建
class OrdersControllerOld extends Controller
{
    /**
     * 6.6 用户订单列表 添加:
     */
    public function index(Request $request){
        $orders = Order::query()
            // 使用 with 方法预加载，避免N + 1问题
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate();

        return view('orders.index', ['orders' => $orders]);
    }

    /**
     * 6.7. 用户订单详情页 添加:
     * 这里的 load() 方法与上一章节介绍的 with() 预加载方法有些类似，称为 延迟预加载，不同点在于 load() 是在已经查询出来的模型上调用，而 with() 则是在 ORM 查询构造器上调用
     */
    public function show(Order $order, Request $request){
        $this->authorize('own', $order); // 6.7. 用户订单详情页 添加

        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

    /**
     * 代码解析:
     * 1.如果回调函数抛出异常则会自动回滚这个事务，否则提交事务。用这个方法可以帮我们节省不少代码。
       2.在事务里先创建了一个订单，把当前用户设为订单的用户，然后把传入的地址数据快照进 address 字段。
       3.然后遍历传入的商品 SKU 及其数量，$order->items()->make() 方法可以新建一个关联关系的对象（也就是 OrderItem）但不保存到数据库，
         这个方法等同于 $item = new OrderItem(); $item->order()->associate($order);。
       4.然后根据所有的商品单价和数量求得订单的总价格，更新到刚刚创建的订单的 total_amount 字段。
       5.最后使用 Laravel 提供的 collect() 辅助函数快速取得所有 SKU ID，然后将本次订单中的商品 SKU 从购物车中删除。
     */
    //public function store(OrderRequest $request){
    public function store(OrderRequest $request,CartService $cartService){ //6.8
        $user  = $request->user();
        // 开启一个数据库事务
        //$order = \DB::transaction(function () use ($user, $request) {
        $order = \DB::transaction(function () use ($user, $request,$cartService) { //6.8
            $address = UserAddress::query()->find($request->input('address_id'));
            // 更新此地址的最后使用时间
            $address->update(['last_used_at' => Carbon::now()]);
            // 创建一个订单
            $order   = new Order([
                'address'      => [ // 将地址信息放入订单中
                    'address'       => $address->full_address,
                    'zip'           => $address->zip,
                    'contact_name'  => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark'       => $request->input('remark'),
                'total_amount' => 0,
            ]);
            // 订单关联到当前用户
            $order->user()->associate($user);
            // 写入数据库
            $order->save();

            $totalAmount = 0;
            $items       = $request->input('items');
            // 遍历用户提交的 SKU
            foreach ($items as $data) {
                $sku  = ProductSku::query()->find($data['sku_id']);
                // 创建一个 OrderItem 并直接与当前订单关联
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price'  => $sku->price,
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount += $sku->price * $data['amount'];
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    //如果减库存失败则抛出异常，由于这块代码是在 DB::transaction() 中执行的，因此抛出异常时会触发事务的回滚，之前创建的 orders 和 order_items 记录都会被撤销
                    throw new InvalidRequestException('该商品库存不足');
                }
            }

            // 更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            // 将下单的商品从购物车中移除
            /*$skuIds = collect($request->input('items'))->pluck('sku_id');
            $user->cartItems()->whereIn('product_sku_id', $skuIds)->delete();*/

            //6.8 修改
            $skuIds = collect($request->input('items'))->pluck('sku_id')->all();
            $cartService->remove($skuIds);

            //6.5 关闭未支付订单 添加:
            $this->dispatch(new CloseOrder($order, config('app.order_ttl')));

            return $order;
        });
        /**
         * 6.8
         * 可以看到与 CartController 通过构造函数参数注入的方式不同，这里我们选择了在方法的参数中注入 CartService 类的对象。
         * 这是因为在 CartController 中所有的方法都会用到 CartService，把 CartService 作为类中的一个属性在调用起来会方便许多；
         * 而 OrdersController 的大多数方法与 CartService 无关，如果把 CartService 对象作为一个属性，那么在请求与 CartService 无关的接口时就会做一些无用的创建类操作，
         * 因此我们选择只在 store() 这个方法的参数里注入。

          通过上面两个例子，我们可以看出来 Laravel 的依赖自动注入无处不在，在我们的开发过程中带来的极大的便利。
         */
        return $order;
    }
}
