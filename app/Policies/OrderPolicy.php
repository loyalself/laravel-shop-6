<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
//6.7. 用户订单详情页 新建
class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 为了安全起见我们只允许订单的创建者可以看到对应的订单信息.
     *
     */
    public function own(User $user, Order $order)
    {
        return $order->user_id == $user->id;
    }
}
