<?php

namespace App\Observers\WorkOrder;

use App\Models\WorkOrder\Reply;
use App\Notifications\WorkOrderNotification;

class ReplyObserver
{
    /**
     * Handle the Reply "created" event.
     *
     * @param Reply $reply
     *
     * @return void|null
     */
    public function created(Reply $reply)
    {
        //
        return (new WorkOrderNotification())
            ->toGroup($reply);
    }

    /**
     * Handle the Reply "updated" event.
     *
     * @param Reply $reply
     *
     * @return void
     */
    public function updated(Reply $reply)
    {
        //
    }

    /**
     * Handle the Reply "deleted" event.
     *
     * @param Reply $reply
     *
     * @return void
     */
    public function deleted(Reply $reply)
    {
        //
    }

    /**
     * Handle the Reply "restored" event.
     *
     * @param Reply $reply
     *
     * @return void
     */
    public function restored(Reply $reply)
    {
        //
    }

    /**
     * Handle the Reply "force deleted" event.
     *
     * @param Reply $reply
     *
     * @return void
     */
    public function forceDeleted(Reply $reply)
    {
        //
    }
}
