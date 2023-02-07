<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Response|RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next): Response|\Illuminate\Http\JsonResponse|RedirectResponse
    {
        // accept json
        $request->headers->set('Accept', 'application/json');

        // set json response
        return $next($request);
    }
}
