<?php

namespace App\Jobs\Subscription;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateSubscriptionStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ?Subscription $subscription;

    public function __construct(?Subscription $subscription = null)
    {
        $this->subscription = $subscription;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->subscription) {
            // 遍历所有订阅
            (new Subscription)->where('status', 'active')->chunk(100, function ($subscriptions) {
                $subscriptions->each(function ($subscription) {
                    self::dispatch($subscription);
                });
            });

            return;
        }

        $subscription = $this->subscription;

        // 如果是试用期，在到期当天，自动续费
        if ($subscription->trial_ends_at && $subscription->trial_ends_at->isToday()) {
            if ($subscription->cancel_at_period_end) {
                // 到期不续费了，直接过期
                $subscription->update([
                    'status' => 'expired',
                ]);
            } else {
                // 去除试用标识
                $subscription->update([
                    'trial_ends_at' => null,
                ]);

                $subscription->renew();
            }

            return;
        }

        // 如果已经过期，则设置为 expired
        if ($subscription->expired_at && $subscription->expired_at->lt(now())) {
            $subscription->update([
                'status' => 'expired',
            ]);

            return;
        }

        // 如果还有 7 天过期，则提醒用户续费
        // if ($subscription->cancel_at_period_end && $subscription->expired_at && $subscription->expired_at->gt(now()->addDays(7))) {
        //
        // }

        // 剩余 3 天就要过期时，自动续费
        if ($subscription->cancel_at_period_end && $subscription->expired_at && $subscription->expired_at->gt(now()->addDays(3))) {
            // 发送邮件提醒用户续费
            $subscription->renew();
        }
    }
}
