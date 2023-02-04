<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ClusterSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->success([
            'message' => 'Welcome to LaeCloud API Server.',
            'node_id' => config('settings.node.id'),
            'ip' => $request->ip(),
        ]);
    }

    public function birthdays(): JsonResponse
    {
        $users = (new User)->birthday()->simplePaginate(20);

        return $this->success($users);
    }

    public function nodes(): JsonResponse
    {
        $nodes = ClusterSupport::nodes(true);

        return $this->success($nodes);
    }
}
