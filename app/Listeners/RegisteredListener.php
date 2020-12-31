<?php

namespace App\Listeners;

use App\Notifications\EmailVerificationNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * 3.3 验证邮箱(下) 新建:
 *我们希望用户在注册完成之后系统就会发送激活邮件,而不是让用户自己去请求激活邮件。
 *我们可以通过 Laravel 的事件系统来完成这个功能，用户注册完成之后会触发一个 Illuminate\Auth\Events\Registered 事件，我们可以创建一个这个事件的监听器（Listener）来发送邮件。
 *
 * 监听器是 Laravel 事件系统的重要组成部分，当一个事件被触发时，对应的监听器就会被执行，可以很方便地解耦代码。
 * 还可以把监听器配置成异步执行，比较适合一些不需要获得返回值并且耗时较长的任务，比如本章节的发送邮件。
 */
//class RegisteredListener
class RegisteredListener implements ShouldQueue // implements ShouldQueue 让这个监听器异步执行
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    // 当事件被触发时，对应该事件的监听器的 handle() 方法就会被调用
    public function handle(Registered $event)
    {
        // 获取到刚刚注册的用户
        $user = $event->user;
        // 调用 notify 发送通知
        $user->notify(new EmailVerificationNotification());
    }
}
