<?php

namespace App\Jobs\Host;

use App\Models\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScanErrorHostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 扫描出错的主机
        (new Host)->whereIn('status', ['error', 'pending', 'unavailable'])->with('module')->chunk(100, function ($hosts) {
            /* @var Host $host */

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
