<?php

namespace App\Jobs\Host;

use App\Models\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// use Illuminate\Support\Facades\Log;

class ScanAllHostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        // 扫描全部主机
        Host::with('module')->chunk(100, function ($hosts) {
            foreach ($hosts as $host) {
                // 忽略维护中的模块
                if ($host->module->status !== 'up') {
                    continue;
                }

                $host->updateOrDelete();
            }
        });
    }
}
