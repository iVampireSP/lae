<?php

namespace App\Http\Controllers\Modules;

use App\Exceptions\User\BalanceNotEnoughException;
use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'sometimes|integer',
            'email' => 'sometimes|email',
            'name' => 'sometimes|string',
        ]);


        $users = User::query();

        // 搜索 name, email, balance
        if ($request->has('name')) {
            $users->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->has('email')) {
            $users->where('email', 'like', '%' . $request->input('email') . '%');
        }

        if ($request->has('balance')) {
            $users->where('balance', 'like', '%' . $request->input('balance') . '%');
        }

        $users = $users->simplePaginate(100);

        return $this->success($users);
    }

    public function show(User $user)
    {
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

        $module = auth('module')->user();
        $transaction = new Transaction();

        try {
            $transaction->reduceAmountModuleFail($user->id, $module->id, $request->amount, $request->description);
        } catch (BalanceNotEnoughException) {
            return $this->error('余额不足');
        }

        return $this->success();
    }
}
