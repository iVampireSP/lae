<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $balance = $user->balance;

        return view('transfer.search', compact('balance'));
    }

    public function transfer(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'string|min:1|max:100',
            'description' => 'nullable|string|max:100',
        ]);

        $to = (new User)->where('email', $request->input('to'))->first();
        if (! $to) {
            return back()->withErrors(['to' => '找不到用户。']);
        }

        $user = $request->user();
        if ($request->input('to') == $user->email) {
            return back()->withErrors(['to' => '不能转给自己。']);
        }

        $amount = $request->input('amount');

        // 使用 bc 判断金额是否足够
        if (bccomp($amount, $user->balance, 2) > 0) {
            return back()->withErrors(['amount' => '余额不足。']);
        }

        $user->startTransfer($to, $amount, $request->input('description'));

        return back()->with('success', '转账成功，已达对方账户。');
    }
}
