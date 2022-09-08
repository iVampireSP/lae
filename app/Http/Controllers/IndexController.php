<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller
{
    //
    public function __invoke()
    {
        return $this->success([
            'message' => 'Welcome to LoliArt LaeCloud API Server',
        ]);
    }
}
