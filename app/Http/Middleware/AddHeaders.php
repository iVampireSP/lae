<?php

namespace App\Http\Middleware;

use App\Support\ClusterSupport;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
