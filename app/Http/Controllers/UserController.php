<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();

        $user['drops'] = (float) Cache::get('user_drops_' . $user['id'], 0);

        return $this->success($user);
    }
}
