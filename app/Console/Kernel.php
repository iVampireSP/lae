<?php

namespace App\Console;

use App\Jobs\AutoCloseWorkOrder;
use App\Jobs\CheckAndChargeBalance;
use App\Jobs\CheckHostIfExistsOnModule;
use App\Jobs\ClearTasks;
use App\Jobs\DeleteHost;
use App\Jobs\HostCost;
use App\Jobs\Module\FetchModule;
use App\Jobs\Module\PushWorkOrder;
use App\Jobs\SendModuleEarnings;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 清理过期的 Token
        $schedule->command('sanctum:prune-expired --hours=24')->daily();

        // 扣费
        $schedule->job(new HostCost(now()->minute))->everyMinute()->withoutOverlapping()->onOneServer();

        // 获取模块暴露的信息（服务器等）
        $schedule->job(new FetchModule())->withoutOverlapping()->everyMinute();

        // 推送工单
        $schedule->job(new PushWorkOrder())->everyMinute()->onOneServer();
        // 自动关闭工单
        $schedule->job(new AutoCloseWorkOrder())->everyMinute()->onOneServer();

        // 清理任务
        $schedule->job(new ClearTasks())->weekly();

        // 删除暂停或部署时间超过 3 天以上的主机
        $schedule->job(new DeleteHost())->hourly();

        // 检查主机是否存在于模块
        $schedule->job(new CheckHostIfExistsOnModule())->everyThirtyMinutes()->withoutOverlapping()->onOneServer();

        // 检查未充值的订单，并充值
        $schedule->job(new CheckAndChargeBalance())->everyFiveMinutes()->onOneServer()->withoutOverlapping();

        // 发送模块收益
        $schedule->job(new SendModuleEarnings())->dailyAt('20:00');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
