<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ModuleController extends Controller
{
    public function index()
    {
        $module = auth('module')->user()->calculate();

        return $this->success($module);
    }

    public function call(Request $request, Module $module)
    {
        $path = request()->path();

        $path = substr($path, strlen('/api/modules/' . $module->id));

        $path = preg_replace('/[^a-zA-Z0-9\/]/', '', $path);

        $method = Str::lower($request->method());

        // 如果 method 为 post, 检查用户余额
        // if ($method == 'post') {
        //     $user = auth()->user();

        //     if ($user->balance < 1) {
        //         return $this->error('账户余额不足，请保证账户余额至少有 1 元。');
        //     }
        // }

        $response = $module->request($method, $path, $request->all());

        if ($response['json'] === null && $response['body'] !== null) {
            return response($response['body'], $response['status']);
        }

        return $this->moduleResponse($response['json'], $response['status']);
    }

    public function exportCall(Request $request, Module $module): Response|JsonResponse
    {
        $path = request()->path();

        $path = substr($path, strlen('/remote/modules/' . $module->id));
        $path = preg_replace('/[^a-zA-Z0-9\/]/', '', $path);

        $method = Str::lower($request->method());

        $response = $module->moduleRequest($method, $path, $request->all());

        if ($response['json'] === null && $response['body'] !== null) {
            return response($response['body'], $response['status']);
        }

        return $this->moduleResponse($response['json'], $response['status']);
    }
}
