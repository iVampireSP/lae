<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ModuleController extends Controller
{
    public function index(): JsonResponse
    {
        $module = auth('module')->user()->calculate();

        return $this->success($module);
    }

    public function call(Request $request, Module $module): Response|JsonResponse
    {
        $path = $this->fixPath($request, $module, 'api');

        $method = Str::lower($request->method());

        $all_files = $request->allFiles();

        if ($all_files) {
            $files = [];
            foreach ($all_files as $key => $file) {
                $files[$key] = [
                    'name' => $file->getClientOriginalName(),
                    'content' => fopen($file->getRealPath(), 'r'),
                ];
            }

            $response = $module->request($method, $path, $request->all(), $files);
        } else {
            $response = $module->request($method, $path, $request->all());
        }

        if ($response['json'] === null && $response['body'] !== null) {
            return response($response['body'], $response['status']);
        }

        return $this->moduleResponse($response['json'], $response['status']);
    }

    private function fixPath(Request $request, Module $module, $prefix): string
    {
        $path = substr($request->path(), strlen("/$prefix/modules/$module->id"));

        // 只允许最基本的字符，以及 _,-
        return preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $path);
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
