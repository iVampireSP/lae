<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();

        $user['drops'] = getDrops($user['id']);
        $user['drops_rate'] = config('drops.rate');

        return $this->success($user);
    }
}
