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
        $user = $request->user();

        $transaction = new Transaction();

        $user['drops'] = $transaction->getDrops($user['id']);
        $user['drops_rate'] = config('drops.rate');


        return $this->success($user);
    }
}
