<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Module\Module;

class ModuleController extends Controller
{
    //

    public function __invoke() {
        $modules = (new Module())->cached_modules();

        return $this->success($modules);
    }
}
