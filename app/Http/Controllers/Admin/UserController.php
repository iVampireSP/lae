<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ChargeException;
use App\Http\Controllers\Controller;
use App\Models\Balance;
use App\Models\Host;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\WorkOrder\WorkOrder;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request): View
    {
        $users = new User();

        if ($request->filled('id')) {
            $users = $users->where('id', $request->id);
        }

        if ($request->filled('name')) {
            $users = $users->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('email')) {
            $users = $users->where('email', 'like', '%' . $request->email . '%');
        }

        $users = $users->with('user_group')->paginate(50)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     *
     * @return RedirectResponse
     */
    public function show(User $user): RedirectResponse
    {
        Auth::guard('web')->login($user);

        return back()->with('success', '您已切换到用户 ' . $user->name . ' 的身份。');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     *
     * @return View
     */
    public function edit(User $user)
    {
        //

        $hosts = Host::where('user_id', $user->id)->latest()->paginate(50, ['*'], 'hosts_page');
        $workOrders = WorkOrder::where('user_id', $user->id)->latest()->paginate(50, ['*'], 'workOrders_page');
        $balances = Balance::where('user_id', $user->id)->latest()->paginate(50, ['*'], 'balances_page');
        $groups = UserGroup::all();

        return view('admin.users.edit', compact('user', 'hosts', 'workOrders', 'balances', 'groups'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User    $user
     *
     * @return RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        //
        $request->validate([
            'balance' => 'nullable|numeric|min:0.01|max:1000',
        ]);

        $transaction = new Transaction();

        if ($request->is_banned) {
            $user->banned_at = Carbon::now();

            if ($request->filled('banned_reason')) {
                $user->banned_reason = $request->banned_reason;
            }
        } else {
            if ($user->banned_at) {
                $user->banned_at = null;
            }
        }

        $one_time_action = $request->input('one_time_action');

        if ($request->filled('one_time_action')) {
            if ($one_time_action == 'clear_all_keys') {
                $user->tokens()->delete();
            } else if ($one_time_action == 'suspend_all_hosts') {
                $user->hosts()->update(['status' => 'suspended', 'suspended_at' => now()]);
            } else if ($one_time_action == 'stop_all_hosts') {
                $user->hosts()->update(['status' => 'stopped', 'suspended_at' => null]);
            } else if ($one_time_action == 'add_balance') {
                try {
                    $transaction->addAmount($user->id, 'console', $request->balance ?? 0, '管理员添加。', true);
                } catch (ChargeException $e) {
                    return back()->with('error', $e->getMessage());
                }
            } else if ($one_time_action == 'reduce_balance') {
                $transaction->reduceAmount($user->id, $request->balance ?? 0, '管理员扣除。');
            }

        }

        if ($request->has('user_group_id')) {
            $user->user_group_id = $request->input('user_group_id');
        }

        if ($user->isDirty()) {
            $user->save();
        }

        return back()->with('success', '已完成所有更改。');
    }
}
