<?php
/**
 * 2.5 辅助函数 新建
 */

/**
 * 2.6 基础布局 添加
 */
function route_class(){
    return str_replace('.', '-', Route::currentRouteName());
}

/**
 * 4.7. 测试支付 添加:
 */
function ngrok_url($routeName, $parameters = []){
    // 开发环境，并且配置了 NGROK_URL
    if(app()->environment('local') && $url = config('app.ngrok_url')) {
        // route() 函数第三个参数代表是否绝对路径
        return $url.route($routeName, $parameters, false);
    }

    return route($routeName, $parameters);
}