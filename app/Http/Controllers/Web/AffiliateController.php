<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Affiliate\Affiliates;
use App\Models\Affiliate\AffiliateUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();
        $user->load('affiliate');

        $affiliate = $user->affiliate;

        // 检测用户是否激活了推介计划
        if (! $affiliate) {
            return redirect()->route('affiliates.create');
        }

        $affiliateUsers = auth()->user()->affiliateUsers()->paginate(10);

        return view('affiliates.index', compact('affiliateUsers', 'affiliate'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|RedirectResponse
    {
        $user = auth('web')->user();
        $user->load('affiliate', 'affiliateUser.affiliate.user');

        if ($user->affiliate) {
            return redirect()->route('affiliates.index')->with('error', '您已经激活了推介计划。');
        }

        return view('affiliates.create', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (auth()->user()->affiliate) {
            return redirect()->route('affiliates.index')->with('error', '您已经激活了推介计划。');
        }

        $request->user('web')->affiliate()->create();

        return redirect()->route('affiliates.index')->with('success', '欢迎您，并感谢您。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Affiliates $affiliate): RedirectResponse
    {
        if (auth('web')->guest()) {
            // save the affiliate id in the session
            session()->put('affiliate_id', $affiliate->id);

            $cache_key = 'affiliate_ip:'.$affiliate->id.':'.request()->ip();

            if (! Cache::has($cache_key)) {
                $affiliate->increment('visits');
                Cache::put($cache_key, true, now()->addHour());
            }
        }

        return redirect()->route('index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Affiliates $affiliate): RedirectResponse
    {
        // 检测是不是自己的推介计划
        if ($affiliate->user_id !== auth()->id()) {
            return redirect()->route('affiliates.index')->with('error', '您没有权限删除此推介计划。');
        }

        AffiliateUser::where('affiliate_id', $affiliate->id)->delete();
        User::where('affiliate_id', $affiliate->id)->update(['affiliate_id' => null]);

        $affiliate->delete();

        return redirect()->route('affiliates.create')->with('success', '推介计划已经成功删除。');
    }
}
