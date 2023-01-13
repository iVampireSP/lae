<?php

namespace App\Jobs\Host;

use App\Models\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// use Illuminate\Contracts\Queue\ShouldBeUnique;

class DeleteHostJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

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
        // 查找暂停时间超过 3 天的 host
        (new Host)->where('status', 'suspended')->where('suspended_at', '<', now()->subDays(3))->chunk(100, function ($hosts) {
            foreach ($hosts as $host) {
                dispatch(new HostJob($host, 'delete'));
            }
        });

        // 查找部署时间超过3天以上的 host
        (new Host)->where('status', 'pending')->where('created_at', '<', now()->subDays(3))->chunk(100, function ($hosts) {
            foreach ($hosts as $host) {
                dispatch(new HostJob($host, 'delete'));
            }
        });
    }
}