<?php

namespace App\Jobs;

use App\Helpers\Lock;
use App\Models\Host;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Log;

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

        // 弃用
        return false;

        Host::all()->chunk(100, function ($hosts) {
            foreach ($hosts as $host) {
                $this->cache_key = 'user_' . $host->user_id;

                // if cache has user

                if (Cache::has($this->cache_key)) {
                    // if user is not instances of Model
                    $user = Cache::get($this->cache_key);

                    Log::debug($user);


                    if ($user instanceof User) {
                        $this->await($this->cache_key, function () use ($user) {
                            $user->save();
                        });
                    }
                } else {
                    // save cache
                    $this->cache->put($this->cache_key, $host->user, now()->addDay());
                }
            }
        });
    }
}
