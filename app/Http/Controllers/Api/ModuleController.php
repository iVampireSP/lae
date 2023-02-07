<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class ModuleController extends Controller
{
    public function index(): JsonResponse
    {
        $modules = Module::all();

        return $this->success($modules);
    }

    public function servers(Module $module): JsonResponse
    {
        $servers = Cache::get('module:'.$module->id.':servers', []);

        return $this->success($servers);
    }

    public function call(Request $request, Module $module): JsonResponse|Response
    {
        return (new \App\Http\Controllers\Module\ModuleController())->call($request, $module);
    }
}
