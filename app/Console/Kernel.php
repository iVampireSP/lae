<?php

namespace App\Console;

use App\Console\Commands\BanUser;
use App\Console\Commands\SuspendUserAllHosts;
use App\Jobs\HostCost;
use App\Jobs\ClearTasks;
use App\Jobs\DeleteHost;
use App\Jobs\Remote;
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
        SuspendUserAllHosts::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //

        // dispatch HostCost job
        $schedule->job(new HostCost())->everyFiveMinutes();
        // $schedule->job(new UserSave())->everyTenMinutes();
        $schedule->job(new Remote\FetchModule())->withoutOverlapping()->everyMinute();
        // $schedule->job(new Remote\PushHost())->everyMinute()->onOneServer();
        $schedule->job(new Remote\PushWorkOrder())->everyMinute()->onOneServer();

        $schedule->job(new ClearTasks())->weekly();

        $schedule->job(new DeleteHost())->hourly();
    }
}
