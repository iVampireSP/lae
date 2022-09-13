<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\ApiResponse;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    use ApiResponse;
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $auth = $this->auth->guard($guard);
        if ($auth->guest()) {
            return $this->unauthorized('Unauthorized.');
        }

        $user = $this->auth->guard($guard)->user();
        if ($user->banned_at) {
            return $this->forbidden('您已被封禁，原因是: ' . $user->banned_reason);
        }

        return $next($request);
    }
}
