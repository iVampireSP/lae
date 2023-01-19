<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ModuleController extends Controller
{
    public function index(): JsonResponse
    {
        $modules = Module::all();

        return $this->success($modules);
    }

    public function call(Request $request, Module $module): JsonResponse|Response
    {
        return (new \App\Http\Controllers\Module\ModuleController())->call($request, $module);
    }
}
