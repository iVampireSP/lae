<?php

namespace App\Observers;

use App\Events\Users;
use App\Models\Subscription;

class SubscriptionObserve
{
    public function creating(Subscription $subscription): void
    {
        // 如果没有设置 status，就设置为 draft
        if (! $subscription->status) {
            $subscription->status = 'draft';
        }
    }

    /**
     * Handle the Subscription "created" event.
     */
    public function created(Subscription $subscription): void
    {
        broadcast(new Users($subscription->user, 'subscription.created', $subscription));
    }

    public function updating(Subscription $subscription): void
    {
        // 如果 status 是 expired, expired_at 为空，就设置为当前时间
        if ($subscription->status === 'expired' && ! $subscription->expired_at) {
            $subscription->expired_at = now();
        }

        // 如果 expired_at 和 trial_ends_at 为空，就当作过期处理
        if ($subscription->status !== 'draft' && ! $subscription->expired_at && ! $subscription->trial_ends_at) {
            $subscription->status = 'expired';
        }
    }

    /**
     * Handle the Subscription "updated" event.
     */
    public function updated(Subscription $subscription): void
    {
        broadcast(new Users($subscription->user, 'subscription.updated', $subscription));
    }

    /**
     * Handle the Subscription "deleted" event.
     */
    public function deleted(Subscription $subscription): void
    {
        broadcast(new Users($subscription->user, 'subscription.deleted', $subscription));
    }
}
