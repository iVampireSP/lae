<?php

namespace App\Http\Controllers\Web;

// use App\Helpers\ApiResponse;

use App\Http\Controllers\Controller;
use App\Notifications\User\UserNotification;
use function back;
use function config;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
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
                    if (! Auth::guard('web')->user()->isRealNamed()) {
                        return redirect()->route('real_name.create')->with('status', '重定向已被打断，需要先实人认证。');
                    }

                    $token = $request->user()->createToken('Dashboard')->plainTextToken;

                    return redirect($callback.'?token='.$token);
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
            'token_name' => 'required|string|max:255',
        ]);

        $token = $request->user()->createToken($request->input('token_name'));

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
}
