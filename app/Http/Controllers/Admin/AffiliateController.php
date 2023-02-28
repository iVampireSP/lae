<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate\Affiliates;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(User $user): View|RedirectResponse
    {
        $affiliate = $user->affiliate;

        // 检测用户是否激活了推介计划
        if (! $affiliate) {
            return redirect()->back()->with('error', '用户未激活推介计划。');
        }

        $affiliateUsers = $user->affiliateUsers()->paginate(10);

        return view('admin.users.affiliates.index', compact('affiliateUsers', 'affiliate', 'user'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, Affiliates $affiliate): RedirectResponse
    {
        $affiliate->delete();

        return redirect()->route('admin.users.edit', $user)->with('success', '成功离开推介计划。');
    }
}
