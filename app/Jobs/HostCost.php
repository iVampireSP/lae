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

    public $cache_key, $cache, $user;

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
        // $this->cache = new Cache();

        // chunk hosts and load user
        Host::active()->with('user')->chunk(1000, function ($hosts) {
            foreach ($hosts as $host) {
                $host->cost();
            }
        });
    }
}
