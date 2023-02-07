<?php

namespace App\Http\Middleware;

use App\Support\ClusterSupport;
use Closure;
use Illuminate\Http\Request;

class AddHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);
        $response->header('Node-Id', ClusterSupport::currentNode()['id']);

        return $response;
    }
}
