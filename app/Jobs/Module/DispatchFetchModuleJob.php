<?php

namespace App\Jobs\Module;

use App\Models\Module;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchFetchModuleJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        (new Module)->whereNotNull('url')->chunk(100, function ($modules) {
            foreach ($modules as $module) {
                dispatch(new FetchModuleJob($module));
            }
        });
    }
}
