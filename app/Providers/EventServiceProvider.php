<?php

namespace App\Providers;

use App\Events\OrderPaid;
use App\Events\OrderReviewd;
use App\Listeners\RegisteredListener;
use App\Listeners\SendOrderPaidMail;
use App\Listeners\UpdateProductRating;
use App\Listeners\UpdateProductSoldCount;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
       /*
        *  default value
         'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],*/

        //3.3 验证邮箱(下) 修改
        Registered::class => [
            RegisteredListener::class,
        ],
        // 7.6. 事件和监听器关联
        OrderPaid::class => [
            UpdateProductSoldCount::class,  //支付成功后增加销量
            SendOrderPaidMail::class,      //支付成功后发送邮件通知
        ],
        //8.5 用户给商品打完分之后，系统重新计算对应商品的评分数据
        OrderReviewd::class => [
            UpdateProductRating::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
