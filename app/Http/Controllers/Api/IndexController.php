<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->success([
            'message' => 'Welcome to LaeCloud API Server.',
            'ip' => $request->ip(),
        ]);
    }

    public function birthdays(): JsonResponse
    {
        $users = User::birthday()->simplePaginate(20);

        return $this->success($users);
    }
}
