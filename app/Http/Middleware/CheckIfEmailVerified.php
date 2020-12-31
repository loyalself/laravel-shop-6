<?php

namespace App\Http\Middleware;

use Closure;

/**
 * 3.2 验证邮箱(上) 新建:
 * 当中间件被执行时，Laravel 会调用中间件的 handle 方法，第一个参数是当前请求对象，第二个参数是执行下一个中间件的闭包函数
 */
class CheckIfEmailVerified
{
    /**
     * 如果当前登录用户的 email_verified 字段不是 true 就将用户重定向到名为 email_verify_notice 的路由，用来提示用户验证邮箱。
     * $next($request) 代表执行下一个中间件
     */
    public function handle($request, Closure $next)
    {
        if (!$request->user()->email_verified) {
            // 如果是 AJAX 请求，则通过 JSON 返回
            if ($request->expectsJson()) {
                return response()->json(['msg' => '请先验证邮箱'], 400);
            }
            return redirect(route('email_verify_notice'));
        }

        return $next($request);
    }
}
