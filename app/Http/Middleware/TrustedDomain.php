<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrustedDomain
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
        $user = $request->user('sanctum');

        if (! $user) {
            return $next($request);
        }

        // 获取请求的域名
        $requestHost = parse_url($request->header('referer'), PHP_URL_HOST);

        if ($requestHost) {
            // 获取当前域名
            $currentHost = parse_url(config('app.url'), PHP_URL_HOST);

            // 如果请求的域名和当前域名相同，则直接放行
            if ($requestHost === $currentHost) {
                return $next($request);
            }

            return $user->tokenCan('domain-access:'.$requestHost) ? $next($request) : response()->json([
                'message' => 'Token 无权访问此域名。',
            ], 401);
        }

        return $next($request);
    }
}
