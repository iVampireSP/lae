<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\RealNameSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class RealNameController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'real_name' => 'required|string',
            'id_card' => 'required|string|size:18|unique:users,id_card',
        ]);

        $realNameSupport = new RealNameSupport();


        $birthday = $realNameSupport->getBirthday($request->input('id_card'));
        // 检查年龄是否在区间内 settings.supports.real_name.min_age ~ settings.supports.real_name.max_age
        if (Carbon::now()->diffInYears($birthday) < config('settings.supports.real_name.min_age') || Carbon::now()->diffInYears($birthday) > config('settings.supports.real_name.max_age')) {
            $message = '至少需要 ' . config('settings.supports.real_name.min_age') . ' 岁，最大 ' . config('settings.supports.real_name.max_age') . ' 岁。';

            return back()->with('error', $message);
        }


        $user = $request->user();

        if ($user->real_name_verified_at !== null) {
            return back()->with('error', '您已经实名认证过了。');
        }

        if ($user->balance < 1) {
            return back()->with('error', '您的余额不足。请保证余额大于 1 元。');
        }

        $output = $realNameSupport->create($user->id, $request->input('real_name'), $request->input('id_card'));

        // 标记用户正在实名，缓存 600s
        if (Cache::has('real_name:user:' . $user->id)) {
            // 获取缓存
            $output = Cache::get('real_name:user:' . $user->id);

            return back()->with('error', '因为您有一个正在进行的实名认证，请等待 10 分钟后重试。')->with('output', $output);
        }

        Cache::set('real_name:user:' . $user->id, $output, 600);

        return redirect($output);
    }

    public function create()
    {
        return view('real_name.create');
    }
}
