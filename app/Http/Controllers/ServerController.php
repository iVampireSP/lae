<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServerController extends Controller
{
    public function __invoke() {
        return $this->success(Cache::get('servers', []));
    }
}
