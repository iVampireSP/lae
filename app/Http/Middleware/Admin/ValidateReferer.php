<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ValidateReferer
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response|RedirectResponse) $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (app()->environment('local')) {
            return $next($request);
        }

        // 如果 referer 不为空，且不是来自本站的请求，则返回 403
        if ($request->headers->get('referer') && ! Str::contains($request->headers->get('referer'), config('app.url'))) {
            abort(403, '来源不属于后台。');
        } else {
            return $next($request);
        }
    }
}
