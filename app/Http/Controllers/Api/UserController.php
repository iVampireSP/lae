<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function attempt(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'nullable|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (! auth()->attempt($credentials)) {
            return $this->error('Invalid credentials', 401);
        }

        $token = auth()->user()->createToken($request->input('name', 'Api Login'))->plainTextToken;

        return $this->success(['token' => $token]);
    }

    public function session(): JsonResponse
    {
        $random = Str::random(64);

        Cache::put('session_login:'.$random, auth()->user()->id, 60);

        return $this->success(['url' => route('auth.fast-login', ['token' => $random])]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user()->load('user_group');

        return $this->success($user);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        $user->update($request->only(['name']));

        return $this->success($user);
    }
}
