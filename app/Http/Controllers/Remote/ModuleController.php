<?php

namespace App\Http\Controllers\Remote;

use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Module\Module;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class ModuleController extends Controller
{
    public function index()
    {
        $module = auth('remote')->user();

        $calc = $this->calcModule($module);

        $data = [
            'module' => $module
        ];

        // merge
        $data = array_merge($data, $calc);

        return $this->success($data);
    }

    public function call(Request $request, Module $module)
    {
        // $this->validate($request, [
        //     'func' => 'required|string'
        // ]);

        // $func = $request->func;

        // // 不能让 func 的首个字符为 /
        // if (Str::startsWith($func, '/')) {
        //     $func = substr($func, 1);
        // }

        $path = request()->path();

        // 删除 modules/{module} 的部分
        $path = substr($path, strlen('/api/modules/' . $module->id));

        // 过滤除了 "/" 以外的特殊字符
        $path = preg_replace('/[^a-zA-Z0-9\/]/', '', $path);


        $method = Str::lower($request->method());

        // 如果 method 为 post, 检查用户余额
        if ($method == 'post') {
            $user = auth('api')->user();

            if ($user->balance < 1) {
                return $this->error('余额小于 1, 无法使用 POST 请求。');
            }
        }


        $response = $module->remoteRequest($method, $path, $request->all());

        if ($response['json'] === null && $response['body'] !== null) {
            return response($response['body'], $response['status']);
        }

        return $this->remoteResponse($response['json'], $response['status']);
    }


    public function calcModule(Module $module)
    {
        // begin of this month
        $beginOfMonth = now()->startOfMonth();

        // end of this month
        $endOfMonth = now()->endOfMonth();

        $this_month_balance_and_drops = Cache::remember('this_month_balance_and_drops_' . $module->id, 3600, function () use ($module, $beginOfMonth, $endOfMonth) {
            $this_month = Transaction::where('module_id', $module->id)->whereBetween('created_at', [$beginOfMonth, $endOfMonth]);

            // this month transactions
            return [
                'balance' => $this_month->sum('outcome'),
                'drops' => $this_month->sum('outcome_drops')
            ];
        });

        $last_month_balance_and_drops = Cache::remember('last_month_balance_and_drops_' . $module->id, 3600, function () use ($module, $beginOfMonth, $endOfMonth) {
            // last month transactions
            $last_moth = Transaction::where('module_id', $module->id)->whereBetween('created_at', [$beginOfMonth, $endOfMonth]);

            return [
                'balance' => $last_moth->sum('outcome'),
                'drops' => $last_moth->sum('outcome_drops')
            ];
        });


        $rate = (int)config('drops.rate') - 10;

        $data = [
            'transactions' => [
                'this_month' => $this_month_balance_and_drops,
                'last_month' => $last_month_balance_and_drops,
            ],
            'balance' => [
                'rate' => $rate,
            ]
        ];


        return $data;
    }
}
