<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    // protected function redirectTo($request)
    // {
    //     if (! $request->expectsJson()) {
    //         return route('login');
    //     }
    // }


    // public function handle($request, Closure $next, $guard = null)
    // {
    //     $auth = $this->auth->guard($guard);
    //     if ($auth->guest()) {
    //         return $this->unauthorized('Unauthorized.');
    //     }

    //     $user = $this->auth->guard($guard)->user();
    //     if ($user->banned_at) {
    //         return $this->forbidden('您已被封禁，原因是: ' . $user->banned_reason ?? '一次或多次触犯了我们的规则。');
    //     }

    //     return $next($request);
    // }
}
