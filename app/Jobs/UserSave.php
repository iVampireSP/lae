<?php

namespace App\Jobs;

use App\Helpers\Lock;
use App\Models\User;
use App\Models\User\Host;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UserSave implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Lock;

    public $cache;
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
        $this->cache = Cache::tags(['users']);

        Host::active()->chunk(100, function ($hosts) {
            foreach ($hosts as $host) {
                $this->cache_key = 'user_' . $host->user_id;

                // if cache has user

                if ($this->cache->has($this->cache_key)) {
                    // if user is not instances of Model
                    $user = $this->cache->get($this->cache_key);
                    if ($user instanceof User) {
                        $this->await($this->cache_key, function () use ($user, $host) {
                            $user->save();
                        });
                    }
                }
            }
        });
    }
}
