<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
