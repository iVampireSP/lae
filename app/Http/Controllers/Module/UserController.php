<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
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

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = (new User)->create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);

        return $this->created($user);
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
        $request->validate([
            'balance' => 'required|numeric|min:-10000|max:10000',
            'description' => 'required|string',
        ]);

        $module = $request->user('module');

        $balance = $request->input('balance');

        if ($balance < 0) {
            // 使用 bc，取 balance 绝对值
            $balance = bcsub(0, $balance, 4);

            if ($user->hasBalance($balance) === false) {
                return $this->error('用户余额不足。');
            }

            $user->reduce($balance, $request->description, true);
            $module->charge($balance, 'balance', $request->description, [
                'user_id' => $user->id,
            ]);
        } else {
            if ($module->hasBalance($balance) === false) {
                return $this->error('模块余额不足。');
            }

            $module->reduce($balance, $request->description, true, [
                'user_id' => $user->id,
                'payment' => 'module_balance',
            ]);

            $user->charge($balance, 'module_balance', $request->description);
        }

        return $this->updated();
    }

    public function auth($token): JsonResponse
    {
        $token = PersonalAccessToken::findToken($token);

        // 画饼: 验证 Token 能力，比如是否可以访问这个模块

        return $token ? $this->success(Arr::only(
            $token->tokenable
                ->makeVisible('real_name')
                ->toArray(),
            [
                'id', 'name', 'email', 'real_name',
            ]
        )) : $this->notFound();
    }
}
