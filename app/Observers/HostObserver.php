<?php

namespace App\Observers;

use App\Events\Users;
use App\Jobs\Host\HostJob;
use App\Models\Host;
use App\Notifications\WebNotification;

class HostObserver
{
    public function creating(Host $host): void
    {
        $host->hour_at = now()->hour;
        $host->minute_at = now()->minute;

        if ($host->price !== null) {
            $host->price = bcdiv($host->price, 1, 2);
        }

        if ($host->managed_price !== null) {
            $host->managed_price = bcdiv($host->managed_price, 1, 2);
        }

        if ($host->billing_cycle !== null) {
            $host->next_due_at = $host->getNewDueDate();
        }
    }

    /**
     * Handle the Host "created" event.
     *
     * @param  Host  $host
     * @return void
     */
    public function created(Host $host): void
    {
        $host->load('module');

        // model price 使用 bcmul 保留两位小数
        $host->price = bcmul($host->price, 1, 2);

        $host->user->notify(new WebNotification($host, 'hosts.created'));
    }

    /**
     * Handle the Host "updated" event.
     *
     * @param  Host  $host
     * @return void
     */
    public function updating(Host $host): void
    {
        if ($host->isDirty('status')) {
            if ($host->status == 'suspended') {
                $host->suspended_at = now();
            } else {
                $host->suspended_at = null;
            }

            if ($host->status == 'locked') {
                $host->locked_at = now();
            } else {
                $host->locked_at = null;
            }

            if ($host->status == 'unavailable') {
                $host->unavailable_at = now();
            } else {
                $host->unavailable_at = null;
            }
        }

        // 调度任务
        if ($host->status !== 'unavailable') {
            dispatch(new HostJob($host, 'patch'));
        }

        broadcast(new Users($host->user_id, 'hosts.updating', $host));
    }

    public function updated(Host $host): void
    {
        broadcast(new Users($host->user_id, 'hosts.updated', $host));
    }

    /**
     * Handle the Host "deleted" event.
     *
     * @param  Host  $host
     * @return void
     */
    public function deleted(Host $host): void
    {
        broadcast(new Users($host->user_id, 'hosts.deleted', $host));
    }
}
