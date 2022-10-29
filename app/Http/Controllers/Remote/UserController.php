<?php

namespace App\Http\Controllers\Remote;

use App\Models\Host;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\User\BalanceNotEnoughException;

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

    public function hosts(User $user)
    {
        $hosts = (new Host())->getUserHosts($user->id);

        return $this->success($hosts);
    }

    public function reduce(Request $request, User $user)
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:0.01|max:10000',
            'description' => 'required|string',
        ]);

        $module = auth('remote')->user();

        $transaction = new Transaction();

        try {
            $transaction->reduceAmountModuleFail($user->id, $module->id, $request->amount, $request->description);
        } catch (BalanceNotEnoughException) {
            return $this->error('余额不足');
        }
    }
}
