<?php

namespace App\Jobs;

use App\Helpers\Lock;
use App\Models\User;
use App\Models\User\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Cache;

class HostCost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Lock;

    public $cache_key, $cache, $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
        $this->cache = Cache::tags(['users']);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // chunk hosts and load user
        Host::active()->with('user')->chunk(100, function ($hosts) {
            foreach ($hosts as $host) {

                $this->cache_key = 'user_' . $host->user_id;

                // if cache has user

                if ($this->cache->has($this->cache_key)) {
                    // if user is not instances of Model
                    $user = $this->cache->get($this->cache_key);
                    if (!($user instanceof User)) {
                        $this->user = $this->cache->put($this->cache_key, $host->user, now()->addDay());
                    } else {
                        $this->user = $user;
                    }
                }

                $this->user->drops -= $host->price;

                // update cache
                $this->cache->put($this->cache_key, $this->user, now()->addDay());
            }
        });
    }
}
