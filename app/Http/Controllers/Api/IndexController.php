<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class IndexController extends Controller
{
    //
    public function __invoke(): JsonResponse
    {
        return $this->success([
            'message' => 'Welcome to LoliArt LaeCloud API Server',
        ]);
    }
}
