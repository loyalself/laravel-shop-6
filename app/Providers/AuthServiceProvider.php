<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\UserAddress;
use App\Policies\OrderPolicy;
use App\Policies\UserAddressPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        // 3.7. 修改和删除收货地址 添加
        UserAddress::class => UserAddressPolicy::class,
        //6.7. 用户订单详情页 添加
        Order::class       => OrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
