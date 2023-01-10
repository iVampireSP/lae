<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Cluster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServerController extends Controller
{
    public function module_reports(Request $request): JsonResponse
    {
        $servers = Cache::get('servers', []);

        if ($request->has('module_id')) {
            // 查找指定 module_id
            $servers = array_filter($servers, function ($server) use ($request) {
                return $server['module_id'] === $request->query('module_id');
            });
        }

        return $this->success($servers);
    }

    public function nodes(): JsonResponse
    {
        $nodes = Cluster::nodes(true);

        $current_node_id = Cluster::currentNode()['id'];

        return $this->success([
            'nodes' => $nodes,
            'current_node_id' => $current_node_id,
        ]);
    }
}
