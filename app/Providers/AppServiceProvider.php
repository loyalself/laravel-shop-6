<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Yansongda\Pay\Pay;
use Elasticsearch\ClientBuilder as ESClientBuilder;
class AppServiceProvider extends ServiceProvider
{

    public function boot(){
        // 3.6. 前台类目菜单 修改:当 Laravel 渲染 products.index 和 products.show 模板时，就会使用 CategoryTreeComposer 这个来注入类目树变量
        // 同时 Laravel 还支持通配符，例如 products.* 即代表当渲染 products 目录下的模板时都执行这个 ViewComposer
        \View::composer(['products.index', 'products.show'], \App\Http\ViewComposers\CategoryTreeComposer::class);

        /**
         * 4.5 下单逻辑 添加:
         * 让 Carbon 对象的 diffForHumans() 方法返回中文,
         * 对于此类需要全局设置的操作，我们通常放在 AppServiceProvider 的 boot() 方法中来执行
         */
        Carbon::setLocale('zh');
    }

    /**
     *  7.1-old. 安装扩展包 yansongda/pay 修改:之前此方法为空
     *  1.容器是现代 PHP 开发的一个重要概念，Laravel 就是在容器的基础上构建的。我们将支付操作类实例注入到容器中，在以后的代码里就可以直接通过 app('alipay') 来取得对应的实例，而不需要每次都重新创建
     *  2.$this->app->singleton() 往服务容器中注入一个单例对象，第一次从容器中取对象时会调用回调函数来生成对应的对象并保存到容器中，之后再去取的时候直接将容器中的对象返回。
        3.app()->environment() 获取当前运行的环境，线上环境会返回 production。对于支付宝，如果项目运行环境不是线上环境，则启用开发模式，并且将日志级别设置为 DEBUG。由于微信支付没有开发模式，所以仅仅将日志级别设置为 DEBUG
     */
    public function register(){
        // 往服务容器中注入一个名为 alipay 的单例对象
        $this->app->singleton('alipay', function () {
            $config = config('pay.alipay');

            /**
             * 注意：回调地址必须是完整的带有域名的 URL，不可以是相对路径。使用 route() 函数生成的 URL 默认就是带有域名的完整地址。
             */
            //$config['notify_url'] = route('payment.alipay.notify');  //7.3 添加: notify_url 代表服务器端回调地址
            $config['return_url'] = route('payment.alipay.return');  //7.3 return_url 代表前端回调地址
            $config['notify_url'] = ngrok_url('payment.alipay.notify'); // 4.7-new. 测试支付 修改

            // 判断当前项目运行环境是否为线上环境
            if (app()->environment() !== 'production') {
                $config['mode']         = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            // 调用 Yansongda\Pay 来创建一个支付宝支付对象
            return Pay::alipay($config);
        });

        $this->app->singleton('wechat_pay', function () {
            $config = config('pay.wechat');
            $config['notify_url'] = ngrok_url('payment.wechat.notify'); //// 4.7-new. 测试支付 添加:

            if (app()->environment() !== 'production') {
                $config['log']['level'] = Logger::DEBUG;
            } else {
                $config['log']['level'] = Logger::WARNING;
            }
            // 调用 Yansongda\Pay 来创建一个微信支付对象
            return Pay::wechat($config);
        });

        // 6.6-new.在 Laravel 中使用 Elasticsearch 添加: 注册一个名为 es 的单例
        $this->app->singleton('es', function () {
            // 从配置文件读取 Elasticsearch 服务器列表
            $builder = ESClientBuilder::create()->setHosts(config('database.elasticsearch.hosts'));
            // 如果是开发环境
            if (app()->environment() === 'local') {
                // 配置日志，Elasticsearch 的请求和返回数据将打印到日志文件中，方便我们调试
                $builder->setLogger(app('log')->getMonolog());
            }
            return $builder->build();
        });
    }
}
