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

        // 如果什么都没有传递，返回用户列表
        if (empty($request->except('page'))) {
            $users = User::simplePaginate(10);
            return $this->success($users);
        }

        if ($request->has('user_id')) {
            return $this->success(User::find($request->user_id));
        }

        // 搜索用户
        $user = User::query();

        if ($request->has('email')) {
            $user->where('email', $request->email);
        }

        if ($request->has('name')) {
            $user->where('name', $request->name);
        }

        return $this->success($user->first());
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
