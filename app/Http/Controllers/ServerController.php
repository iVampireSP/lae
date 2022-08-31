<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServerController extends Controller
{
    public function __invoke(Request $request) {

        $servers = Cache::get('servers', []);
        //

        if ($request->has('module_id')) {
            // 查找指定 module_id
            $servers = array_filter($servers, function ($server) use ($request) {
                return $server['module_id'] === $request->query('module_id');
            });
        }

        return $this->success($servers);
    }
}
