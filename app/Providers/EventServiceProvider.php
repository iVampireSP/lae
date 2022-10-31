<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // \App\Events\WorkOrderProcessed::class => [
        //     \App\Listeners\SendWorkOrderNotification::class,
        // ],
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }

    public function boot() {
        parent::boot();

        \App\Models\WorkOrder\WorkOrder::observe(\App\Observers\WorkOrder\WorkOrderObserver::class);
        \App\Models\WorkOrder\Reply::observe(\App\Observers\WorkOrder\ReplyObserver::class);
        \App\Models\User\Balance::observe(\App\Observers\BalanceObserve::class);
    }
}
