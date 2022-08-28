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

    public function call(Request $request, Module $module, $func)
    {
        $request->validate([
            'func' => 'required|string'
        ]);

        // 不能让 func 的首个字符为 /
        if (Str::startsWith($func, '/')) {
            $func = substr($func, 1);
        }

        $response = $module->remote($func, $request->all());

        return $this->apiResponse($response[0], $response[1]);
    }
}
