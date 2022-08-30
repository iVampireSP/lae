<?php

namespace App\Http\Controllers;

use Cache;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();

        $user['drops'] = (float) Cache::get('user_drops_' . $user['id'], 0);

        return $this->success($user);
    }
}
