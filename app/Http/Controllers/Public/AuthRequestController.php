<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Rules\Domain;
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
            'require_token' => 'nullable|boolean',
            'abilities' => 'nullable|array|max:255',
        ]);

        $token = Str::random(128);

        $data = [
            'meta' => [
                'description' => $request->input('description'),
                'token' => $token,
                'require_token' => $request->input('require_token', false),
                'abilities' => $request->input('abilities'),
            ],
        ];

        if ($request->user('module')) {
            $data['module'] = $request->user('module')->toArray();
        }

        if ($request->user('application')) {
            $data['application'] = $request->user('application')->toArray();
        }

        if ($request->user('sanctum')) {
            $data['from_user'] = $request->user('sanctum')->getOnlyPublic([
                'balance',
            ]);
        }

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
