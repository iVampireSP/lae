<?php

namespace App\Console;

use App\Jobs\HostCost;
use App\Jobs\ClearTasks;
use App\Jobs\DeleteHost;
use App\Jobs\Remote\FetchModule;
use App\Jobs\Remote\PushWorkOrder;
use App\Jobs\CheckAndChargeBalance;
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
        $schedule->command('sanctum:prune-expired --hours=24')->daily();

        // dispatch HostCost job
        $schedule->job(new HostCost())->everyFiveMinutes();
        // $schedule->job(new UserSave())->everyTenMinutes();
        $schedule->job(new FetchModule())->withoutOverlapping()->everyMinute();
        // $schedule->job(new Remote\PushHost())->everyMinute()->onOneServer();
        $schedule->job(new PushWorkOrder())->everyMinute()->onOneServer();

        $schedule->job(new ClearTasks())->weekly();

        $schedule->job(new DeleteHost())->hourly();

        $schedule->job(new CheckAndChargeBalance())->everyFiveMinutes()->onOneServer()->withoutOverlapping();
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
