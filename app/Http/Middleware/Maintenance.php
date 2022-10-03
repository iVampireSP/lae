<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class Maintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        // if cache has system down
        if (Cache::has('system_down')) {
            return response()->json([
                'message' => '我们正在进行维护，请稍等 2 小时后再来。',
            ], 503);
        }


        // continue
        return $next($request);
    }
}
