<?php

namespace App\Http\Middleware;

use App\Support\ClusterSupport;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ReportRequestToCluster
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $random_id = Str::random(16);

        $method = $request->method();
        $path = $request->path();

        // ClusterSupport::publish('http.incoming', [
        //     'id' => $random_id,
        //     'method' => $method,
        //     'path' => $path,
        //     'query' => $request->query(),
        //     'body' => $request->all(),
        //     // 'headers' => $request->headers->all(),
        //     'ip' => $request->ip(),
        //     'user-agent' => $request->userAgent(),
        //     'user' => $request->user(),
        // ]);

        $start = microtime(true);

        $response = $next($request);

        $end = microtime(true);

        $status = 0;
        // 检测有没有 status() 方法。
        if (method_exists($response, 'status')) {
            $status = $response->status();
        }

        ClusterSupport::publish('http.outgoing', [
            'id' => $random_id,
            'method' => $method,
            'status' => $status,
            'path' => $path,
            // 'headers' => $response->headers->all(),
            // 'content' => $response->getContent(),
            'user' => $request->user(),
            'time' => round(($end - $start) * 1000),
        ]);

        return $response;
    }
}
