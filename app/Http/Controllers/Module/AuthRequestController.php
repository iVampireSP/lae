<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AuthRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'description' => 'required|string|max:255',
        ]);

        $token = Str::random(128);

        $data = [
            'description' => $request->input('description'),
            'token' => $token,
            'module' => $request->user('module')->toArray(),
        ];

        Cache::put('auth_request:'.$token, $data, 120);

        $data['url'] = route('auth_request.show', $token);

        return $this->success($data);
    }

    public function show($token): JsonResponse
    {
        $data = Cache::get('auth_request:'.$token);

        if (empty($data)) {
            return $this->error('Token 不存在或已过期。');
        }

        if (! isset($data['user'])) {
            $data['user'] = null;
        }

        return $this->success($data);
    }
}
