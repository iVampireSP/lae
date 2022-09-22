<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogAllRequest
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

        Log::debug('request', [
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'data' => $request->all(),
        ]);
        // Pre-Middleware Action

        $response = $next($request);

        // Post-Middleware Action
        // Log::debug('response', $response->content);


        return $response;
    }
}
