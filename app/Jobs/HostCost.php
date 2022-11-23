<?php

namespace App\Jobs;

use App\Helpers\Lock;
use App\Models\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HostCost implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Lock;

    public $minute, $cache, $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($minute)
    {
        //
        $this->minute = $minute;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // chunk hosts and load user
        Host::where('minute_at', $this->minute)->whereIn('status', ['running', 'stopped'])->with('user')->chunk(500, function ($hosts) {
            foreach ($hosts as $host) {
                $host->cost();
            }
        });

        // Host::whereIn('status', ['running', 'stopped'])->with('user')->chunk(1000, function ($hosts) {
        //     foreach ($hosts as $host) {
        //         $host->cost();
        //     }
        // });
    }
}
