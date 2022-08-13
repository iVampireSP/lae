<?php

namespace App\Helpers;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

trait Lock {
    public function await($name, Closure $callback) {
        // if env is local
        if (env('APP_ENV') == 'local') {
            return $callback();
        }
        $lock = Cache::lock("lock_" . $name, 5);
        try {
            $lock->block(5);
            return $callback();
        } catch (LockTimeoutException $e) {
            return false;
        } finally {
            optional($lock)->release();
        }
    }
}
