<?php

namespace App\Jobs\User;

use App\Models\User;
use App\Models\UserGroup;
use App\Notifications\User\TodayIsUserBirthday;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetBirthdayGroupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $birthday_group = (new UserGroup)->find(config('settings.user_groups.birthday_group_id'));

        if (! $birthday_group) {
            return;
        }

        // 先撤销原来的
        (new User)->where('user_group_id', $birthday_group->id)->update(['user_group_id' => null]);

        (new User)->birthday()->whereNull('user_group_id')->chunk(100, function ($users) use ($birthday_group) {
            foreach ($users as $user) {
                $user->user_group_id = $birthday_group->id;
                $user->save();

                $user->notify(new TodayIsUserBirthday());
            }
        });
    }
}
