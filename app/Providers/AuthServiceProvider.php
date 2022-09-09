<?php

namespace App\Providers;

use App\Models\AccessToken;
use App\Models\Module\Module;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
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
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.


        // api guard and remote

        $this->app['auth']->viaRequest('api', function ($request) {
            // if ($request->input('api_token')) {
            //     return AccessToken::where('token', $request->input('api_token'))->with('user')->first()->user ?? null;
            // }
            // bearerToken
            $bearerToken = $request->bearerToken();

            return Cache::remember('api_token_' . $bearerToken, 60, function () use ($bearerToken) {
                return AccessToken::where('token', $bearerToken)->with('user')->first()->user ?? null;
            });

            // if ($request->input('api_token')) {
            //     return User::where('api_token', $request->input('api_token'))->first();
            // }
        });

        $this->app['auth']->viaRequest('remote', function ($request) {

            // if ($request->input('api_token')) {
            //     return Module::where('api_token', $request->input('api_token'))->first();
            // }
            // bearerToken
            $bearerToken = $request->bearerToken();

            return Cache::remember('api_token_' . $bearerToken, 60, function () use ($bearerToken) {
                return Module::where('token', $bearerToken)->first() ?? null;
            });

            // if ($request->input('api_token')) {
            //     return User::where('api_token', $request->input('api_token'))->first();
            // }
        });
    }
}
