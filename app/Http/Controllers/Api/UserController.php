<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use function config;

class UserController extends Controller
{

    public function index(Request $request)
    {
        return $this->success($request->user());
    }
}
