<?php

namespace App\Http\Controllers\Remote;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ModuleController extends Controller
{
    public function index()
    {
        $module = auth('remote')->user();

        $calc = $this->calcModule($module);

        $data = [
            'module' => $module,
            'rate' => (int) config('drops.module_rate'),
        ];

        // merge
        $data = array_merge($data, $calc);

        return $this->success($data);
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


        $response = $module->remoteRequest($method, $path, $request->all());

        if ($response['json'] === null && $response['body'] !== null) {
            return response($response['body'], $response['status']);
        }

        return $this->remoteResponse($response['json'], $response['status']);
    }


    public function exportCall(Request $request, Module $module): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $path = request()->path();

        $path = substr($path, strlen('/remote/modules/' . $module->id));
        $path = preg_replace('/[^a-zA-Z0-9\/]/', '', $path);

        $method = Str::lower($request->method());

        $response = $module->moduleRequest($method, $path, $request->all());

        if ($response['json'] === null && $response['body'] !== null) {
            return response($response['body'], $response['status']);
        }

        return $this->remoteResponse($response['json'], $response['status']);
    }


    public function calcModule(Module $module): array
    {

        $default = [
            'balance' => 0,
            'drops' => 0,
        ];

        return [
            'transactions' => [
                'this_month' => Cache::get('this_month_balance_and_drops_' . $module->id, $default),
                'last_month' => Cache::get('last_month_balance_and_drops_' . $module->id, $default),
            ]
        ];
    }
}
