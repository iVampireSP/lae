<?php

namespace App\Observers;

use App\Models\Affiliate\AffiliateUser;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class UserObserver
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function creating(User $user): void
    {
        $user->email_md5 = md5($user->email);
        $user->uuid = Str::uuid();

        // if session has affiliate_id, then set it to user
        if (session()->has('affiliate_id')) {
            $user->affiliate_id = session()->get('affiliate_id');
        }
    }

    public function created(User $user): void
    {
        // if user has affiliate_id, then create an affiliate_user record
        if ($user->affiliate_id) {
            AffiliateUser::create([
                'affiliate_id' => $user->affiliate_id,
                'user_id' => $user->id,
            ]);
        }
    }

    public function updating(User $user): void
    {
        if ($user->isDirty('banned_at')) {
            if ($user->banned_at) {
                $user->tokens()->delete();
                $user->hosts()->update(['status' => 'suspended', 'suspended_at' => now()]);
            } else {
                $user->hosts()->update(['status' => 'stopped']);
            }
        }

        if ($user->isDirty('email')) {
            $user->email_md5 = md5($user->email);
        }

        if ($user->isDirty('id_card') || $user->isDirty('real_name')) {
            if (empty($user->id_card) || empty($user->real_name)) {
                $user->real_name_verified_at = null;
            } else {
                $user->real_name_verified_at = now();
                $user->id_card = Crypt::encryptString($user->id_card);

                $user->birthday_at = $user->getBirthdayFromIdCard();
            }
        }
    }

    public function deleting(User $user): void
    {
        $user->tokens()->delete();
        $user->hosts()->update(['status' => 'suspended', 'suspended_at' => now()]);
    }
}
