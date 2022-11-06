<?php

namespace App\Jobs;

use App\Models\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// use Illuminate\Contracts\Queue\ShouldBeUnique;

class DeleteHost implements ShouldQueue
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
    public function handle()
    {
        //

        // 查找暂停时间超过3天以上的 host
        Host::where('status', 'suspended')->where('suspended_at', '<', now()->subDays(3))->chunk(100, function ($hosts) {
            foreach ($hosts as $host) {
                dispatch(new \App\Jobs\Remote\Host($host, 'delete'));
            }
        });
    }
}
