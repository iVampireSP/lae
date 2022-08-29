<?php

namespace App\Http\Controllers\Remote;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Module\Module;
use App\Http\Controllers\Controller;

class ModuleController extends Controller
{
    public function index()
    {
        return $this->success(auth('remote')->user());
    }

    public function call(Request $request, Module $module)
    {
        $request->validate([
            'func' => 'required|string'
        ]);

        $func = $request->func;

        // 不能让 func 的首个字符为 /
        if (Str::startsWith($func, '/')) {
            $func = substr($func, 1);
        }

        // 过滤除了 "/" 以外的特殊字符
        $func = preg_replace('/[^a-zA-Z0-9\/]/', '', $func);
        


        // dd($func);

        $method = Str::lower($request->method());


        $response = $module->remoteRequest($method, $func, $request->all());

        if ($response['json'] === null && $response['body'] !== null) {
            return response($response['body'], $response['status']);
        }

        return $this->apiResponse($response['json'], $response['status']);
    }
}
