<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = (new User)->paginate(10);

        return $this->success($users);
    }

    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);

        return $this->created($user);
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
