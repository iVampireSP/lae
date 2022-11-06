<?php

namespace App\Http\Middleware;

use Closure;

class AllowCors
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
        $headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, PATCH, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
            'Access-Control-Max-Age' => '86400',

            'server' => 'Cluster Ready!',
            'x-powered-by' => 'LaeCloud',
            'x-for-you' => 'Code is Poetry.',
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}
