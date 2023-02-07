<?php

namespace App\Helpers;

use Closure;
use Illuminate\Support\Facades\Cache;

trait Lock
{
    public function await($name, Closure $callback)
    {
        // if env is local
        if (config('app.env') == 'local') {
            return $callback();
        }
        $lock = Cache::lock('lock_' . $name, 5);
        try {
            $lock->block(5);

            return $callback();
        } finally {
            optional($lock)->release();
        }
    }
}
