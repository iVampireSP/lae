<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserGroup;
use App\Notifications\TodayIsUserBirthday;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetBirthdayGroupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $birthday_group = UserGroup::find(config('settings.user_groups.birthday_group_id'));

        if (!$birthday_group) {
            return;
        }

        User::birthday()->chunk(100, function ($users) use ($birthday_group) {
            foreach ($users as $user) {
                // 到第二天 00:00 now
                $now = now()->addDay()->startOfDay();

                $birthday_group->setTempGroup($user, $birthday_group, $now);
                $user->notify(new TodayIsUserBirthday());
            }
        });
    }
}
