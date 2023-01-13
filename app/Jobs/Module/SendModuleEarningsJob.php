<?php

namespace App\Jobs\Module;

use App\Jobs\Job;
use App\Models\Module;
use App\Notifications\Modules\ModuleEarnings;

class SendModuleEarningsJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        (new Module)->chunk(100, function ($modules) {
            foreach ($modules as $module) {
                (new ModuleEarnings($module))
                    ->toGroup($module->calculate());
            }
        });

    }
}
