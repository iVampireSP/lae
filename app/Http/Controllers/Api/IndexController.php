<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class IndexController extends Controller
{
    //
    public function index(): JsonResponse
    {
        return $this->success([
            'message' => 'Welcome to LaeCloud API Server.',
        ]);
    }

    public function birthdays(): JsonResponse
    {
        // 获取今天过生日的用户，每页显示 20 个,使用 carbon
        $users = User::birthday()->simplePaginate(20);

        return $this->success($users);
    }
}
