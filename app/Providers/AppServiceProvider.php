<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
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
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->withOptions([
                    'version' => 2,
                ]);
        });

        // Carbon setTestNow
        // Carbon::setTestNow(now()->addDays(1));
    }
}
