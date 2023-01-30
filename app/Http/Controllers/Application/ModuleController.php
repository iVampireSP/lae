<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\JsonResponse;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = (new Module)->all()->makeVisible(['api_token', 'url']);

        return $this->success($modules);
    }

    public function show(Module $module): JsonResponse
    {
        return $this->success($module);
    }
}
