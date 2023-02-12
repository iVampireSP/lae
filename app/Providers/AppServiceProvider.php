<?php

namespace App\Providers;

use App\Models\Balance;
use App\Models\Host;
use App\Models\Module;
use App\Models\PersonalAccessToken;
use App\Models\Task;
use App\Models\User;
use App\Observers\BalanceObserver;
use App\Observers\HostObserver;
use App\Observers\ModuleObserver;
use App\Observers\TaskObserver;
use App\Observers\UserObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Http::macro('module', function ($api_token, $url) {
            // 关闭证书验证
            return Http::baseUrl($url)
                ->withUserAgent('LAECloud-Client')
                ->withHeaders([
                    'X-Module-Api-Token' => $api_token,
                ])->withOptions([
                    'version' => 2,
                ]);
        });

        // Carbon::setTestNow(now()->addDays(1));

        $this->registerObservers();
    }

    private function registerObservers(): void
    {
        User::observe(UserObserver::class);
        Host::observe(HostObserver::class);
        Task::observe(TaskObserver::class);
        Module::observe(ModuleObserver::class);
        Balance::observe(BalanceObserver::class);
    }
}
