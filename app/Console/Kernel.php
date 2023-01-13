<?php

namespace App\Console;

use App\Jobs\Host\DeleteHostJob;
use App\Jobs\Host\HostCostJob;
use App\Jobs\Host\ScanAllHostsJob;
use App\Jobs\Module\FetchModuleJob;
use App\Jobs\Module\SendModuleEarningsJob;
use App\Jobs\User\CheckAndChargeBalanceJob;
use App\Jobs\User\ClearTasksJob;
use App\Jobs\User\RollbackUserTempGroupJob;
use App\Jobs\User\SetBirthdayGroupJob;
use App\Jobs\WorkOrder\AutoCloseWorkOrderJob;
use App\Jobs\WorkOrder\PushWorkOrderJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // 清理过期的 Token
        $schedule->command('sanctum:prune-expired --hours=24')->daily();

        // 扣费
        $schedule->job(new HostCostJob(now()->minute))->everyMinute()->withoutOverlapping()->onOneServer();

        // 获取模块暴露的信息（服务器等）
        $schedule->job(new FetchModuleJob())->withoutOverlapping()->everyMinute();

        // 推送工单
        $schedule->job(new PushWorkOrderJob())->everyMinute()->onOneServer();
        // 自动关闭工单
        $schedule->job(new AutoCloseWorkOrderJob())->everyMinute()->onOneServer();

        // 清理任务
        $schedule->job(new ClearTasksJob())->weekly();

        // 删除暂停或部署时间超过 3 天以上的主机
        $schedule->job(new DeleteHostJob())->hourly();

        // 检查主机是否存在于模块
        $schedule->job(new ScanAllHostsJob())->everyThirtyMinutes()->withoutOverlapping()->onOneServer();

        // 检查未充值的订单，并充值
        $schedule->job(new CheckAndChargeBalanceJob())->everyFiveMinutes()->onOneServer()->withoutOverlapping();

        // 发送模块收益
        $schedule->job(new SendModuleEarningsJob())->dailyAt('20:00');

        // 回滚临时用户组
        $schedule->job(new RollbackUserTempGroupJob())->everyMinute()->onOneServer();

        // 设置生日用户组
        $schedule->job(new SetBirthdayGroupJob())->dailyAt('00:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
