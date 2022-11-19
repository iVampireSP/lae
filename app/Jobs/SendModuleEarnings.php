<?php

namespace App\Jobs;

use App\Models\Module;
use App\Notifications\ModuleEarnings;
use Illuminate\Support\Facades\Cache;

class SendModuleEarnings extends Job
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
    public function handle()
    {
        //


        Module::chunk(100, function ($modules) {
            foreach ($modules as $module) {
                (new ModuleEarnings($module))
                    ->toGroup($module->calculate());
            }
        });

    }
}
