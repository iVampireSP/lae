<?php

namespace App\Console;

use App\Console\Commands\BanUser;
use App\Console\Commands\CalcModule;
use App\Console\Commands\Cluster;
use App\Console\Commands\Count;
use App\Console\Commands\GetUser;
use App\Console\Commands\ReduceBalance;
use App\Console\Commands\Status;
use App\Console\Commands\SuspendUserAllHosts;
use App\Console\Commands\UnbanUser;
use App\Console\Commands\UserAddBalance;
use App\Jobs\AutoCloseWorkOrder;
use App\Jobs\CalcModule as JobsCalcModule;
use App\Jobs\CheckAndChargeBalance;
use App\Jobs\HostCost;
use App\Jobs\ClearTasks;
use App\Jobs\DeleteHost;
use App\Jobs\Remote;
use App\Jobs\SendThisMonthModuleEarnings;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        BanUser::class,
        UnbanUser::class,
        SuspendUserAllHosts::class,
        CalcModule::class,
        UserAddBalance::class,
        GetUser::class,
        ReduceBalance::class,
        Count::class,
        Status::class,
        Cluster\Init::class,
        Cluster\Send::class,
        Cluster\Work::class,
        Cluster\Sync::class,
        Commands\System\Down::class,
        Commands\System\Up::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // dispatch HostCost job
        $schedule->job(new HostCost())->everyFiveMinutes();
        // $schedule->job(new UserSave())->everyTenMinutes();
        $schedule->job(new Remote\FetchModule())->withoutOverlapping()->everyMinute();
        // $schedule->job(new Remote\PushHost())->everyMinute()->onOneServer();
        $schedule->job(new Remote\PushWorkOrder())->everyMinute()->onOneServer();

        $schedule->job(new ClearTasks())->weekly();

        $schedule->job(new DeleteHost())->hourly();

        $schedule->job(new CheckAndChargeBalance())->everyFiveMinutes()->onOneServer()->withoutOverlapping();

        $schedule->job(new AutoCloseWorkOrder())->everyFiveMinutes();

        $schedule->job(new JobsCalcModule())->everyFiveMinutes();

        // 每天晚上 20 点，发送模块收益
        $schedule->job(new SendThisMonthModuleEarnings())->dailyAt('20:00');
    }
}
