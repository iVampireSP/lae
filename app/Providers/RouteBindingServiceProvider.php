<?php

namespace App\Providers;

use mmghv\LumenRouteBinding\RouteBindingServiceProvider as BaseServiceProvider;

class RouteBindingServiceProvider extends BaseServiceProvider
{
    /**
     * Boot the service provider
     */
    public function boot()
    {
        // The binder instance
        $binder = $this->binder;

        $binder->implicitBind('App\Models');
        $binder->implicitBind('App\Models\Module');
        $binder->implicitBind('App\Models\Admin');
        $binder->implicitBind('App\Models\Server');
        $binder->implicitBind('App\Models\User');
        $binder->implicitBind('App\Models\WorkOrder');

        // Here we define our bindings
        // $binder->bind('user', 'App\Models\User');
        // $binder->bind(
        //     'module',
        //     'App\Models\Module\Module'
        // );
        // $binder->bind(
        //     'workOrder',
        //     'App\Models\WorkOrder\WorkOrder'
        // );
        // $binder->bind('reply', 'App\Models\WorkOrder\Reply');
        // $binder->bind('task', 'App\Models\');
    }
}
