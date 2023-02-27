<?php

namespace App\Http\Controllers\Public;

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
            'require_token' => 'nullable|boolean',
            'abilities' => 'nullable|array|max:255',
            'return_url' => 'nullable|url',
        ]);

        if ($request->filled('return_url') && $request->hasHeader('referer')) {
            // 如果有 referer，检查是否和来源域名一致
            $referer = parse_url($request->header('referer'), PHP_URL_HOST);

            // return url 的域名
            $returnUrl = parse_url($request->input('return_url'), PHP_URL_HOST);

            if ($referer !== $returnUrl) {
                return $this->error('来源域名不匹配。');
            }
        }

        $token = Str::random(128);

        $data = [
            'meta' => [
                'description' => $request->input('description'),
                'token' => $token,
                'require_token' => $request->input('require_token', false),
                'abilities' => $request->input('abilities'),
                'return_url' => $request->input('return_url'),
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
