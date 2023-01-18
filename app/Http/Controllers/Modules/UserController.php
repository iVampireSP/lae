<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
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

    public function show(User $user): JsonResponse
    {
        return $this->success($user);
    }

    public function hosts(User $user): JsonResponse
    {
        $hosts = (new Host())->getUserHosts($user->id);

        return $this->success($hosts);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->validate($request, [
            'balance' => 'required|numeric|min:-10000|max:10000',
            'description' => 'required|string',
        ]);

        $module = $request->user('module');

        $balance = $request->input('balance');

        if ($balance < 0) {
            // 使用 bc，取 balance 绝对值
            $balance = bcsub(0, $balance, 4);

            // 如果用户余额不足，抛出异常，使用 bc 函数判断
            if (bccomp($user->balance, $balance, 2) === -1) {
                return $this->error('用户余额不足。');
            }

            $user->reduce($balance, $request->description, true);
            $module->charge($balance, 'balance', $request->description, [
                'user_id' => $user->id,
            ]);
        } else {
            // 如果模块余额不足，抛出异常，使用 bc 函数判断
            if (bccomp($module->balance, $balance, 2) === -1) {
                return $this->error('模块余额不足。');
            }

            $module->reduce($balance, $request->description, true, [
                'user_id' => $user->id,
                'payment' => 'module_balance'
            ]);

            $user->charge($balance, 'module_balance', $request->description);
        }

        return $this->updated();
    }

}
