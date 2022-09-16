<?php

namespace App\Http\Controllers;

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
