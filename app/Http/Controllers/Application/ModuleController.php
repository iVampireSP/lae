<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    //

    // public function index() {
    //     $modules = Module::all();
    //
    //     return $this->success($modules);
    // }


    public function show(Module $module) {


        return $this->success($module);

        // $module = Module::find(request()->route('module'));
        //
        // return $this->success($module);
    }
}