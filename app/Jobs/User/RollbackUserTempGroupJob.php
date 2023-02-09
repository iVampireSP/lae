<?php

namespace App\Jobs\User;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class RollbackUserTempGroupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        //

        $temp_groups = Cache::get('users_temp_groups', []);

        foreach ($temp_groups as $user_id => $temp_group) {
            if (now()->gt($temp_group['expired_at'])) {
                $user = (new User)->find($user_id);
                $user->user_group_id = $temp_group['user_group_id'];
                $user->save();
                unset($temp_groups[$user_id]);
            }
        }

        Cache::forever('users_temp_groups', $temp_groups);
    }
}
