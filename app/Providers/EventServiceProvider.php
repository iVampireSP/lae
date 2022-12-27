<?php

namespace App\Providers;

use App\Models\Balance;
use App\Models\WorkOrder\Reply;
use App\Models\WorkOrder\WorkOrder;
use App\Observers\BalanceObserve;
use App\Observers\WorkOrder\ReplyObserver;
use App\Observers\WorkOrder\WorkOrderObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //

        WorkOrder::observe(WorkOrderObserver::class);
        Reply::observe(ReplyObserver::class);
        Balance::observe(BalanceObserve::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
