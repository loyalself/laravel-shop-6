<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Auth\Access\HandlesAuthorization;
// 3.7. 修改和删除收货地址 新建
class UserAddressPolicy
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
     * 当 own() 方法返回 true 时代表当前登录用户可以修改对应的地址.
     * 接下来还需要在 AuthServiceProvider 注册这个授权策略：
     */
    public function own(User $user, UserAddress $address){
        return $address->user_id == $user->id;
    }
}
