<?php

namespace App\Http\Controllers\Remote;

use App\Models\Host;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    //


    public function show(User $user)
    {
        $transaction = new Transaction();

        $user['drops'] = $transaction->getDrops($user['id']);
        $user['drops_rate'] = config('drops.rate');

        return $this->success($user);
    }

    public function hosts(Request $request, User $user)
    {
        $hosts = (new Host())->getUserHosts($request->module_id ?? null);

        return $this->success($hosts);
    }
}
