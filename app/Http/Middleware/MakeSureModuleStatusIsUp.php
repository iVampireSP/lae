<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MakeSureModuleStatusIsUp
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {
        $user = $request->user('module');
        if ($user && $user->status !== 'up') {
            return response()->json([
                'message' => '无法连接到模块。',
            ], 503);
        }

        return $next($request);
    }
}
