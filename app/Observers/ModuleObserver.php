<?php

namespace App\Observers;

use App\Models\Module;
use Illuminate\Support\Str;

class ModuleObserver
{
    public function creating(Module $module): void
    {
        if (! app()->environment('local')) {
            $module->api_token = Str::random(60);
        }

        // 如果设置了 url 并且结尾有 / 则去掉
        if ($module->url) {
            $module->url = rtrim($module->url, '/');
        }
    }

    public function updating(Module $module): void
    {
        $module->url = rtrim($module->url, '/');
    }
}
