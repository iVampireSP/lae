<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RealNamed
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $guard = 'web'): Response|RedirectResponse|JsonResponse
    {
        // 检测用户是否登录
        if (auth($guard)->check()) {
            if ($request->user($guard)->real_name_verified_at === null) {
                if ($request->expectsJson()) {
                    return $this->unauthorized('您还没有实名认证。');
                }

                return redirect()->route('real_name.create');
            }
        }

        return $next($request);
    }
}
