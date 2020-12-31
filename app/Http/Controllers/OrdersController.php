<?php

namespace App\Http\Controllers;
use App\Events\OrderReviewd;
use App\Exceptions\CouponCodeUnavailableException;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\ApplyRefundRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\SendReviewRequest;
use App\Models\CouponCode;
use App\Models\UserAddress;
use App\Models\Order;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\OrderServiceOld;
//6.8. 封装业务代码 修改
class OrdersController extends Controller
{
    public function index(Request $request){
        $orders = Order::query()
            // 使用 with 方法预加载，避免N + 1问题
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate();

        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Order $order, Request $request){
        $this->authorize('own', $order); // 6.7. 用户订单详情页 添加

        return view('orders.show', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }

   /* public function store(OrderRequest $request, OrderServiceOld $orderService){
      $user    = $request->user();
      $address = UserAddress::query()->find($request->input('address_id'));
      return $orderService->store($user, $address, $request->input('remark'), $request->input('items'));
    }*/

    //9.4 用户界面 - 使用优惠券下单 修改
    public function store(OrderRequest $request, OrderService $orderService)
    {
        $user    = $request->user();
        $address = UserAddress::query()->find($request->input('address_id'));
        $coupon  = null;

        // 如果用户提交了优惠码
        if ($code = $request->input('coupon_code')) {
            $coupon = CouponCode::query()->where('code', $code)->first();
            if (!$coupon) {
                throw new CouponCodeUnavailableException('优惠券不存在');
            }
        }
        // 参数中加入 $coupon 变量
        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'), $coupon);
    }


    /**
     * 8.4. 用户确认收货 添加:
     */
    public function received(Order $order, Request $request){
        // 校验权限
        $this->authorize('own', $order);
        // 判断订单的发货状态是否为已发货
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRequestException('发货状态不正确');
        }
        // 更新发货状态为已收到
        $order->update(['ship_status' => Order::SHIP_STATUS_RECEIVED]);
        // 返回原页面
        //return redirect()->back();

        //8.4 由于我们把确认收货的操作从表单提交改成了 AJAX 请求,所以这里返回订单信息
        return $order;
    }
    // 8.5 添加: 评价商品页面
    public function review(Order $order)
    {
        // 校验权限
        $this->authorize('own', $order);
        // 判断是否已经支付
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }
        // 使用 load 方法加载关联数据，避免 N + 1 性能问题
        return view('orders.review', ['order' => $order->load(['items.productSku', 'items.product'])]);
    }
    //8.5 添加:评价商品逻辑
    public function sendReview(Order $order, SendReviewRequest $request)
    {
        // 校验权限
        $this->authorize('own', $order);
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }
        // 判断是否已经评价
        if ($order->reviewed) {
            throw new InvalidRequestException('该订单已评价，不可重复提交');
        }
        $reviews = $request->input('reviews');
        // 开启事务
        \DB::transaction(function () use ($reviews, $order) {
            // 遍历用户提交的数据
            foreach ($reviews as $review) {
                $orderItem = $order->items()->find($review['id']);
                // 保存评分和评价
                $orderItem->update([
                    'rating'      => $review['rating'],
                    'review'      => $review['review'],
                    'reviewed_at' => Carbon::now(),
                ]);
            }
            event(new OrderReviewd($order));  // 8.5. 评价商品 添加
            // 将订单标记为已评价
            $order->update(['reviewed' => true]);
        });

        return redirect()->back();
    }
    /**
     * 8.6. 用户界面 - 申请退款 添加:
     */
    public function applyRefund(Order $order, ApplyRefundRequest $request){
        // 校验订单是否属于当前用户
        $this->authorize('own', $order);
        // 判断订单是否已付款
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可退款');
        }
        // 判断订单退款状态是否正确
        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已经申请过退款，请勿重复申请');
        }
        // 将用户输入的退款理由放到订单的 extra 字段中
        $extra                  = $order->extra ?: [];
        $extra['refund_reason'] = $request->input('reason');
        // 将订单退款状态改为已申请退款
        $order->update([
            'refund_status' => Order::REFUND_STATUS_APPLIED,
            'extra'         => $extra,
        ]);
        return $order;
    }


}

/**
 * 6.8 关于 Service 模式:
   Service 模式将 PHP 的商业逻辑写在对应责任的 Service 类里，解決 Controller 臃肿的问题。
 * 并且符合 SOLID 的单一责任原则，购物车的逻辑由 CartService 负责，而不是 CartController ，控制器是调度中心，编码逻辑更加清晰。
 * 后面如果我们有 API 或者其他会使用到购物车功能的需求，也可以直接使用 CartService ，代码可复用性大大增加。
 * 再加上 Service 可以利用 Laravel 提供的依赖注入机制，大大提高了 Service 部分代码的可测试性，程序的健壮性越佳。
 *
 */