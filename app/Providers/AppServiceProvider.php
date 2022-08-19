<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //

        Http::macro('remote', function ($api_token, $url) {
            return Http::withHeaders([
                'X-Remote-Api-Token' => $api_token,
            ])->baseUrl($url);
        });

    }
}
