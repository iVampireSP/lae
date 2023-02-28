<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ResourceOwner
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $model): mixed
    {
        $model = $request->route($model);

        if ($model && isset($model->user_id) && $request->user()) {
            // if module has user_id and user is logined
            if ($model->user_id != $request->user()->id) {
                abort(403);
            }
        }

        return $next($request);
    }
}
