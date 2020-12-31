<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');
    $router->get('users', 'UsersController@index'); // 用户列表
    $router->get('products', 'ProductsController@index'); //  后台商品列表
    $router->get('products/create', 'ProductsController@create'); //  创建商品页面
    $router->post('products', 'ProductsController@store'); // 创建商品逻辑
    $router->get('products/{id}/edit', 'ProductsController@edit'); //修改商品页面
    $router->put('products/{id}', 'ProductsController@update'); // 修改商品逻辑
    $router->get('orders', 'OrdersController@index')->name('admin.orders.index'); // 订单列表
    $router->get('orders/{order}', 'OrdersController@show')->name('admin.orders.show'); //订单详情
    $router->post('orders/{order}/ship', 'OrdersController@ship')->name('admin.orders.ship'); //
    $router->post('orders/{order}/refund', 'OrdersController@handleRefund')->name('admin.orders.handle_refund'); //拒绝退款
    $router->get('coupon_codes', 'CouponCodesController@index'); //优惠券列表
    $router->post('coupon_codes', 'CouponCodesController@store'); //新增优惠券
    $router->get('coupon_codes/create', 'CouponCodesController@create'); //新增优惠券页面
    $router->get('coupon_codes/{id}/edit', 'CouponCodesController@edit'); //修改优惠券页面
    $router->put('coupon_codes/{id}', 'CouponCodesController@update'); //修改优惠券
    $router->delete('coupon_codes/{id}', 'CouponCodesController@destroy'); //删除优惠券

    $router->get('categories', 'CategoriesController@index'); //3.3 商品分类首页
    $router->get('categories/create', 'CategoriesController@create'); //3.3 创建商品分类
    $router->get('categories/{id}/edit', 'CategoriesController@edit'); //3.3 编辑某个商品分类页面
    $router->post('categories', 'CategoriesController@store'); //3.3 保存商品分类
    $router->put('categories/{id}', 'CategoriesController@update'); //3.3 更新某个商品分类
    $router->delete('categories/{id}', 'CategoriesController@destroy'); //3.3 删除某个商品分类
    $router->get('api/categories', 'CategoriesController@apiIndex'); //3.3 获取商品分类
});
