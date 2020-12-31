<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');
    $router->get('users', 'UsersController@index'); //4.2 用户列表
    $router->get('products', 'ProductsController@index'); // 5.2. 后台商品列表
    $router->get('products/create', 'ProductsController@create'); // 5.3 创建商品页面
    $router->post('products', 'ProductsController@store'); //5.3 创建商品逻辑
    $router->get('products/{id}/edit', 'ProductsController@edit'); //5.3 修改商品页面
    $router->put('products/{id}', 'ProductsController@update'); //5.3 修改商品逻辑
    $router->get('orders', 'OrdersController@index')->name('admin.orders.index'); //8.1 订单列表
    $router->get('orders/{order}', 'OrdersController@show')->name('admin.orders.show'); //8.2 订单详情
    $router->post('orders/{order}/ship', 'OrdersController@ship')->name('admin.orders.ship'); //8.3
    $router->post('orders/{order}/refund', 'OrdersController@handleRefund')->name('admin.orders.handle_refund'); //8.7 拒绝退款
    $router->get('coupon_codes', 'CouponCodesController@index'); //9.1. 优惠券列表
    $router->post('coupon_codes', 'CouponCodesController@store'); //9.2 新增优惠券
    $router->get('coupon_codes/create', 'CouponCodesController@create'); //9.2 新增优惠券页面
    $router->get('coupon_codes/{id}/edit', 'CouponCodesController@edit'); //9.2 修改优惠券页面
    $router->put('coupon_codes/{id}', 'CouponCodesController@update'); //9.2 修改优惠券
    $router->delete('coupon_codes/{id}', 'CouponCodesController@destroy'); //9.2 删除优惠券
});
