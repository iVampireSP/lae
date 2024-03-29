<?php

namespace App\Observers;

use App\Events\Users;
use App\Models\Balance;
use App\Notifications\User\UserCharged;

class BalanceObserver
{
    public function creating(Balance $balance): void
    {
        $balance->remaining_amount = 0;

        $balance->order_id = date('YmdHis').'-'.$balance->user_id.'-'.rand(1000, 9999);
    }

    public function created(Balance $balance): void
    {
        broadcast(new Users($balance->user, 'balance.created', $balance));
    }

    public function updated(Balance $balance): void
    {
        if ($balance->isDirty('paid_at')) {
            if ($balance->paid_at) {
                $balance->load('user');
                $balance->load('user.affiliateUser');

                $balance->notify(new UserCharged());
                broadcast(new Users($balance->user, 'balance.updated', $balance));

                $balance->user->charge($balance->amount, $balance->payment, $balance->order_id);

                if ($balance->user->affiliate_id) {
                    $balance->user->affiliateUser->addRevenue($balance->amount);
                }
            }
        }
    }
}
