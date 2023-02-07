<?php

namespace App\Http\Controllers\Web;

// use App\Helpers\ApiResponse;

use App\Http\Controllers\Controller;
use App\Notifications\User\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use function back;
use function config;
use function redirect;
use function session;
use function view;

class AuthController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        // if logged in
        if ($request->filled('callback')) {
            $callback = $request->input('callback');

            session(['callback' => $callback]);

            if (Auth::guard('web')->check()) {
                $callbackHost = parse_url($callback, PHP_URL_HOST);
                $dashboardHost = parse_url(config('settings.dashboard.base_url'), PHP_URL_HOST);

                if ($callbackHost === $dashboardHost) {
                    if (!Auth::guard('web')->user()->isRealNamed()) {
                        return redirect()->route('real_name.create')->with('status', '重定向已被打断，需要先实人认证。');
                    }

                    $token = $request->user()->createToken('Dashboard')->plainTextToken;

                    return redirect($callback . '?token=' . $token);
                }

                return redirect()->route('confirm_redirect');
            } else {
                return redirect()->route('login');
            }
        }

        return view('index');
    }

    public function confirm_redirect(Request $request): View
    {
        $callback = $request->callback ?? session('callback');

        return view('confirm_redirect', compact('callback'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = $request->user('web');

        $user->update([
            'name' => $request->input('name'),
        ]);

        return back()->with('success', '更新成功。');
    }

    public function newToken(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $token = $request->user()->createToken($request->input('name'));

        return back()->with('token', $token->plainTextToken);
    }

    public function deleteAll(Request $request): RedirectResponse
    {
        $user = $request->user('web');

        $user->tokens()->delete();
        $user->notify(new UserNotification('莱云', '您的所有 Token 已被删除。'));

        return back()->with('success', '所有 Token 删除成功。');
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('web')->logout();

        session()->regenerateToken();

        return redirect()->route('index');
    }

    public function exitSudo(): RedirectResponse
    {
        session()->forget('auth.password_confirmed_at');

        return back();
    }

    public function showAuthRequest($token): View|RedirectResponse
    {
        $data = Cache::get('auth_request:' . $token);

        if (empty($data)) {
            return redirect()->route('index')->with('error', '登录请求的 Token 不存在或已过期。');
        }

        if (isset($data['user'])) {
            return redirect()->route('index')->with('error', '登录请求的 Token 已被使用。');
        }

        // 登录后跳转的地址
        session(['url.intended' => route('auth_request.show', $token)]);

        return view('auth.request', [
            'data' => $data,
        ]);
    }

    public function storeAuthRequest(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required|string|max:128',
        ]);

        $data = Cache::get('auth_request:' . $request->input('token'));

        if (empty($data)) {
            return back()->with('error', '登录请求的 Token 不存在或已过期。');
        }

        if (isset($data['user'])) {
            return back()->with('error', '登录请求的 Token 已被使用。');
        }

        $data['user'] = $request->user('web');

        Cache::put('auth_request:' . $request->input('token'), $data, 60);

        return redirect()->route('index')->with('success', '登录请求已确认。');
    }
}
