<?php

namespace App\Console;

use App\Jobs\ClearTasks;
use App\Jobs\Remote;
use App\Jobs\HostCost;
use App\Jobs\UserSave;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // dispatch HostCost job
        $schedule->job(new HostCost())->everyFiveMinutes();
        // $schedule->job(new UserSave())->everyTenMinutes();
        $schedule->job(new Remote\FetchModule())->everyMinute()->onOneServer();
        $schedule->job(new Remote\PushHost())->everyMinute()->onOneServer();
        $schedule->job(new Remote\PushWorkOrder())->everyMinute()->onOneServer();

        $schedule->job(new ClearTasks())->weekly();


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
