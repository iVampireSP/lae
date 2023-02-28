<?php

namespace App\Console;

use App\Jobs\Host\DeleteHostJob;
use App\Jobs\Host\DispatchHostCostQueueJob;
use App\Jobs\Host\ScanAllHostsJob;
use App\Jobs\Module\DispatchFetchModuleJob;
use App\Jobs\Module\SendModuleEarningsJob;
use App\Jobs\Subscription\DeleteDraftJob;
use App\Jobs\Subscription\UpdateSubscriptionStatusJob;
use App\Jobs\User\CheckAndChargeBalanceJob;
use App\Jobs\User\ClearTasksJob;
use App\Jobs\User\DeleteUnverifiedUserJob;
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
     */
    protected function schedule(Schedule $schedule): void
    {
        // 清理过期的 Token
        $schedule->command('sanctum:prune-expired --hours=24')->daily()->runInBackground()->onOneServer()->name('清理过期的 Token。');

        // 扣费
        $schedule->job(new DispatchHostCostQueueJob(now()->minute))->everyMinute()->withoutOverlapping()->onOneServer()->name('部署扣费任务');

        // 获取模块暴露的信息（服务器等,检查模块状态）
        $schedule->job(new DispatchFetchModuleJob())->withoutOverlapping()->everyMinute()->name('获取模块暴露的信息（服务器等,检查模块状态）');

        // 推送工单
        $schedule->job(new PushWorkOrderJob())->everyMinute()->onOneServer()->name('推送工单');
        // 自动关闭工单
        $schedule->job(new AutoCloseWorkOrderJob())->everyMinute()->onOneServer()->name('自动关闭工单');

        // 清理任务
        $schedule->job(new ClearTasksJob())->weekly()->onOneServer()->name('清理大于 1 天的任务');

        // 删除暂停或部署时间超过 3 天以上的主机
        $schedule->job(new DeleteHostJob())->hourly()->onOneServer()->name('删除暂停或部署时间超过 3 天以上的主机');

        // 检查主机是否存在于模块
        $schedule->job(new ScanAllHostsJob())->everyThirtyMinutes()->withoutOverlapping()->onOneServer()->name('检查主机是否存在于模块');

        // 检查未充值的订单，并充值
        $schedule->job(new CheckAndChargeBalanceJob())->everyFiveMinutes()->onOneServer()->withoutOverlapping()->name('检查未充值的订单，并充值');

        // 发送模块收益
        $schedule->job(new SendModuleEarningsJob())->dailyAt('20:00')->onOneServer()->name('发送模块收益');

        // 回滚临时用户组
        $schedule->job(new RollbackUserTempGroupJob())->everyMinute()->onOneServer()->name('回滚临时用户组');

        // 设置生日用户组
        $schedule->job(new SetBirthdayGroupJob())->dailyAt('00:00')->onOneServer()->name('设置生日用户组');

        // 删除注册超过 3 天未验证邮箱的用户
        $schedule->job(new DeleteUnverifiedUserJob())->daily()->onOneServer()->name('删除注册超过 3 天未验证邮箱的用户');

        // 订阅
        $schedule->job(new DeleteDraftJob())->daily()->onOneServer()->name('删除超过 1 天的草稿订阅');
        $schedule->job(new UpdateSubscriptionStatusJob())->everyMinute()->onOneServer()->name('更新订阅状态');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
