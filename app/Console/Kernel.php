<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * 注：Laravel 5.5 版本起，我们不再需要手动去注册 Artisan 命令，Laravel 会自动扫描 app/Console/Commands/ 目录下的代码并加载。
     * 即不需要去  protected $commands 里添加命令
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        // 每分钟执行一次
        $schedule->command('cron:finish-crowdfunding')->everyMinute(); //4.8-new. 众筹结束逻辑 添加
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
