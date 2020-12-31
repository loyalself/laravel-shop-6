<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', 'PagesController@root')->name('root');  //2.6 首页
Route::redirect('/', '/products')->name('root');  //5.4 修改首页为商品首页
Route::get('products', 'ProductsController@index')->name('products.index'); //5.4
//Route::get('products/{product}', 'ProductsController@show')->name('products.show');   //5.6 商品详情页

// 3.1 注册与登录 php artisan make:auth 生成以下两条路由
Auth::routes();
//Route::get('/home', 'HomeController@index')->name('home');

// 3.2 验证邮箱(上) 添加:
Route::group(['middleware' => 'auth'], function() {
    Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');     //3.2 提醒邮箱验证
    Route::get('/email_verification/verify', 'EmailVerificationController@verify')->name('email_verification.verify'); //3.3 邮箱验证
    Route::get('/email_verification/send', 'EmailVerificationController@send')->name('email_verification.send');  //3.3 手动发送邮件

    Route::group(['middleware' => 'email_verified'], function() {
        //Route::get('/test', function() { return 'Your email is verified';});
        Route::get('user_addresses', 'UserAddressesController@index')->name('user_addresses.index'); // 3.5 收获地址列表
        Route::get('user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create'); // 3.6. 收货地址页面
        Route::post('user_addresses', 'UserAddressesController@store')->name('user_addresses.store'); // 3.6. 新增收货地址
        Route::get('user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit'); //3.7. 修改收货地址页面
        Route::put('user_addresses/{user_address}', 'UserAddressesController@update')->name('user_addresses.update'); //3.7 修改收货地址逻辑
        Route::delete('user_addresses/{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy'); //3.7 删除收货地址
        Route::post('products/{product}/favorite', 'ProductsController@favor')->name('products.favor');  //5.7. 收藏商品
        Route::delete('products/{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor'); //5.7. 取消收藏
        Route::get('products/favorites', 'ProductsController@favorites')->name('products.favorites'); //5.7 收藏商品列表
        Route::post('cart', 'CartController@add')->name('cart.add'); //5.8 添加商品到购物车
        Route::get('cart', 'CartController@index')->name('cart.index'); //6.2 用户购物车页面
        Route::delete('cart/{sku}', 'CartController@remove')->name('cart.remove'); //6.2 从购物车中移除商品
        Route::post('orders', 'OrdersController@store')->name('orders.store'); // 6.4 购物车下单
        Route::get('orders', 'OrdersController@index')->name('orders.index'); //6.6 用户订单列表
        Route::get('orders/{order}', 'OrdersController@show')->name('orders.show'); // 6.7. 用户订单详情页
        Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay'); //7.3. 订单的支付宝支付
        Route::get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return'); // 7.3 支付宝前端回调
        Route::post('orders/{order}/received', 'OrdersController@received')->name('orders.received'); // 8.4 用户确认收货
        Route::get('orders/{order}/review', 'OrdersController@review')->name('orders.review.show');   //8.5 评价商品页面
        Route::post('orders/{order}/review', 'OrdersController@sendReview')->name('orders.review.store'); //8.5 评价商品
        Route::post('orders/{order}/apply_refund', 'OrdersController@applyRefund')->name('orders.apply_refund'); //8.6 申请退款
        Route::get('coupon_codes/{code}', 'CouponCodesController@show')->name('coupon_codes.show'); //9.3 检查优惠券

        Route::post('crowdfunding_orders', 'OrdersController@crowdfunding')->name('crowdfunding_orders.store'); //4.5-new. 下单逻辑
    });
});

Route::get('products/{product}', 'ProductsController@show')->name('products.show');

// 7.2. 集成支付宝 测试
Route::get('alipay', function() {
    return app('alipay')->web([
        'out_trade_no' => time(),
        'total_amount' => '1',
        'subject' => 'test subject - 测试',
    ]);
});
//7.3. 支付宝回调: 服务器端回调的路由不能放到带有 auth 中间件的路由组中，因为支付宝的服务器请求不会带有认证信息
Route::post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');