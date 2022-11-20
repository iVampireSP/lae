<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    //

    public function index() {
        $modules = Module::paginate(10);

        return view('admin.index', compact('modules'));
    }
}
