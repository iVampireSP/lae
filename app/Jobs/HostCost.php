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
        Host::active()->with('user')->chunk(100, function ($hosts) {
            foreach ($hosts as $host) {

                $host->cost();

                //     $this->cache_key = 'user_' . $host->user_id;

                //     // if cache has user

                //     if ($this->cache->has($this->cache_key)) {
                //         // if user is not instances of Model
                //         $user = $this->cache->get($this->cache_key);

                //         if ($user instanceof User) {
                //             $this->user = $user;
                //         } else {
                //             $this->user = $this->cache->put($this->cache_key, $host->user, now()->addDay());
                //         }
                //     } else {
                //         $this->user = $this->cache->put($this->cache_key, $host->user, now()->addDay());
                //     }

                //     // Log::debug($user);

                //     if ($host->managed_price) {
                //         $host->price = $host->managed_price;
                //     }


                //     $this->user->drops -= $host->price;

                //     // update cache
                //     $this->cache->put($this->cache_key, $this->user, now()->addDay());
            }
        });
    }
}
