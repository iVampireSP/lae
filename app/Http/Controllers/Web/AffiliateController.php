<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Affiliate\Affiliates;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user('web');
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
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user('web');
        $user->load('affiliate', 'affiliateUser.affiliate.user');

        if ($user->affiliate) {
            return redirect()->route('affiliates.index')->with('error', '您已经激活了推介计划。');
        }

        return view('affiliates.create', compact('user'));
    }

    /**
     * Display the specified resource.
     */
    public function show($affiliate): RedirectResponse
    {
        $redirect = redirect()->route('register');

        $affiliate = Affiliates::where('uuid', $affiliate)->first();

        if (auth('web')->guest() && $affiliate) {
            session()->put('affiliate_id', $affiliate->id);

            $cache_key = 'affiliate_ip:'.$affiliate->id.':'.request()->ip();

            if (! Cache::has($cache_key)) {
                $affiliate->increment('visits');
                Cache::put($cache_key, true, now()->addHour());
            }
        } else {
            $redirect->with('error', '此推介链接已失效。');
        }

        return $redirect;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Affiliates $affiliate): RedirectResponse
    {
        $affiliate->delete();

        return redirect()->route('affiliates.create')->with('success', '推介计划已经成功删除。');
    }
}
