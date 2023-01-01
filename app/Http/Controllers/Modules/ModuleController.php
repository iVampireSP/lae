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
        $path = $this->fixPath($request, $module, 'api');

        $method = Str::lower($request->method());

        $response = $module->request($method, $path, $request->all());

        if ($response['json'] === null && $response['body'] !== null) {
            return response($response['body'], $response['status']);
        }

        return $this->moduleResponse($response['json'], $response['status']);
    }

    private function fixPath(Request $request, Module $module, $prefix): string
    {
        $path = substr($request->path(), strlen("/{$prefix}/modules/{$module->id}"));

        return preg_replace('/[^a-zA-Z0-9\/]/', '', $path);
    }

    public function exportCall(Request $request, Module $module): Response|JsonResponse
    {
        $path = $this->fixPath($request, $module, 'modules');
        $method = Str::lower($request->method());

        $response = $module->moduleRequest($method, $path, $request->all());

        if ($response['json'] === null && $response['body'] !== null) {
            return response($response['body'], $response['status']);
        }

        return $this->moduleResponse($response['json'], $response['status']);
    }
}
