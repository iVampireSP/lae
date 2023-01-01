<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware(['api', 'auth:sanctum'])
                ->prefix('api')
                ->as('api.')
                ->group(base_path('routes/api.php'));

            Route::middleware(['module', 'auth:module'])
                ->prefix('modules')
                ->as('modules.')
                ->group(base_path('routes/modules.php'));

            Route::middleware(['module', 'auth:application'])
                ->prefix('applications')
                ->as('applications.')
                ->group(base_path('routes/applications.php'));

            Route::middleware(['web', 'admin.validateReferer'])
                ->prefix('admin')
                ->as('admin.')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            // return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());

            // 只限制登录用户
            return Limit::perMinute(60)->by($request->user()?->id);
        });
    }
}
