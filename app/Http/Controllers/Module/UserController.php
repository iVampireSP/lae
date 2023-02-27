<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

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
            $users->where('name', 'like', '%'.$request->input('name').'%');
        }

        if ($request->has('email')) {
            $users->where('email', 'like', '%'.$request->input('email').'%');
        }

        if ($request->has('balance')) {
            $users->where('balance', 'like', '%'.$request->input('balance').'%');
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
            'unique_id' => 'nullable|string',
        ]);

        $module = $request->user('module');

        if ($request->filled('balance')) {
            if ($request->filled('unique_id')) {
                $unique_id_cache_key = 'module:'.$request->user('module')->id.':balance:unique_id:'.$request->input('unique_id');
                if (Cache::has($unique_id_cache_key)) {
                    return $this->conflict('重复的请求。');
                }
            }

            $balance = $request->input('balance');

            if ($balance < 0) {
                // 使用 bc，取 balance 绝对值
                $balance = bcsub(0, $balance, 4);

                if ($user->hasBalance($balance) === false) {
                    return $this->error('用户余额不足。');
                }

                $trans = $user->reduce($balance, $request->description, true, [
                    'module_id' => $module->id,
                    'payment' => 'balance',
                ]);
                $module->charge($balance, 'module_balance', $request->description, [
                    'user_id' => $user->id,
                ]);
            } else {
                $balance = bcsub($balance, 0, 4);

                if ($module->hasBalance($balance) === false) {
                    return $this->error('模块余额不足。');
                }

                $module->reduce($balance, $request->description, true, [
                    'user_id' => $user->id,
                ]);
                $trans = $user->charge($balance, 'balance', $request->description, [
                    'module_id' => $module->id,
                ]);
            }

            if ($request->filled('unique_id')) {
                $unique_id_cache_key = 'module:'.$request->user('module')->id.':balance:unique_id:'.$request->input('unique_id');
                Cache::put($unique_id_cache_key, $trans->id, now()->addDay());
            }
        }

        $trans['commission'] = config('settings.billing.commission');

        return $this->success($trans);
    }

    public function auth($token): JsonResponse
    {
        $token = PersonalAccessToken::findToken($token);

        // 画饼: 验证 Token 能力，比如是否可以访问这个模块

        return $token ? $this->success(Arr::only(
            $token->tokenable
                ->makeVisible(['real_name', 'email_verified_at', 'real_name_verified_at'])
                ->toArray(),
            [
                'id', 'name', 'email', 'real_name', 'email_verified_at', 'real_name_verified_at',
            ]
        )) : $this->notFound();
    }

    public function attempt(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'require_token' => 'nullable|boolean',
            'abilities' => 'nullable|array',
        ]);

        // 验证
        $user = User::where('email', $request->input('email'))->first();
        if ($user === null) {
            return $this->error('用户不存在。');
        }

        if (password_verify($request->input('password'), $user->password) === false) {
            return $this->error('密码错误。');
        }

        if ($request->input('require_token')) {
            $user['token'] = $user->createToken('模块创建', $request->input('abilities', ['*']))->plainTextToken;
        }

        return $this->success($user);
    }
}
