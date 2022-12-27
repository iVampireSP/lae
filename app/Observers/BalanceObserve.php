<?php

namespace App\Observers;

use App\Models\Balance;
use App\Notifications\UserBalanceNotification;

class BalanceObserve
{
    /**
     * Handle the Balance "created" event.
     *
     * @param Balance $balance
     *
     * @return array
     */
    public function created(Balance $balance)
    {
        //
        return (new UserBalanceNotification())
            ->toGroup($balance);
    }

    /**
     * Handle the Balance "updated" event.
     *
     * @param Balance $balance
     *
     * @return array
     */
    public function updated(Balance $balance)
    {
        //
        return (new UserBalanceNotification())
            ->toGroup($balance);
    }

    /**
     * Handle the Balance "deleted" event.
     *
     * @param Balance $balance
     *
     * @return void
     */
    public function deleted(Balance $balance)
    {
        //
    }

    /**
     * Handle the Balance "restored" event.
     *
     * @param Balance $balance
     *
     * @return void
     */
    public function restored(Balance $balance)
    {
        //
    }

    /**
     * Handle the Balance "force deleted" event.
     *
     * @param Balance $balance
     *
     * @return void
     */
    public function forceDeleted(Balance $balance)
    {
        //
    }
}
