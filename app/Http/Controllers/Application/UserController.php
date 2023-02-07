<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = (new User)->paginate(10);

        return $this->success($users);
    }

    public function show(User $user): JsonResponse
    {
        return $this->success($user);
    }

    public function auth($token): JsonResponse
    {
        $token = PersonalAccessToken::findToken($token);

        return $token ? $this->success($token->tokenable) : $this->notFound();
    }
}
