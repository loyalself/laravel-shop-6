<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //7.3 添加: 这个 URL 是给支付宝服务器调用的，肯定不会有 CSRF Token，所以需要把这个 URL 加到 CSRF 白名单里
        'payment/alipay/notify',
        //5.6. 分期还款（支付宝） 添加:
        'installments/alipay/notify',
    ];
}
