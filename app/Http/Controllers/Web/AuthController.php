<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\User\UserNotification;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
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

            if ($request->user('web')) {
                $callbackHost = parse_url($callback, PHP_URL_HOST);
                $dashboardHost = parse_url(config('settings.dashboard.base_url'), PHP_URL_HOST);

                if ($callbackHost === $dashboardHost) {
                    if (!$request->user('web')->isRealNamed()) {
                        return redirect()->route('real_name.create')->with('status', '重定向已被打断，需要先实人认证。');
                    }

                    $token = $request->user()->createToken('Dashboard')->plainTextToken;

                    return redirect($callback . '?token=' . $token);
                }

                session(['referer.domain' => parse_url($request->header('referer'), PHP_URL_HOST)]);

                return redirect()->route('confirm_redirect');
            } else {
                // url.intended 存放当前页面 URL
                session(['url.intended' => $request->fullUrl()]);

                return redirect()->route('login')->with('status', '要继续，请先登录账号。');
            }
        }

        return $request->user('web') ? view('index') : view('auth.login');
    }

    public function confirm_redirect(Request $request): View
    {
        $callback = $request->callback ?? session('callback');

        $referer_host = session('referer.domain');

        return view('confirm_redirect', compact('callback', 'referer_host'));
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => 'nullable|sometimes|string|max:255',
            'receive_marketing_email' => 'nullable|sometimes|boolean',
        ]);

        $user = $request->user('web');

        $user->update($request->only('name', 'receive_marketing_email'));

        if ($request->ajax()) {
            return $this->success($user->only('name', 'receive_marketing_email'));
        }

        return back()->with('success', '更新成功。');
    }

    public function newToken(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $token = $request->user()->createToken(
            $request->input('name'),
        );

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

    public function storeAuthRequest(Request $request): RedirectResponse|View
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

        $user = $request->user('web');

        $data['user'] = $user->getOnlyPublic([], [
            'email',
            'email_verified_at',
            'real_name_verified_at',
        ]);

        $abilities = $data['meta']['abilities'] ?? ['*'];

        if (isset($data['meta']['require_token']) && $data['meta']['require_token']) {
            $data['token'] = $user->createToken($data['meta']['description'] ?? Carbon::now()->toDateString(), $abilities)->plainTextToken;
        }

        Cache::put('auth_request:' . $request->input('token'), $data, 60);

        if (isset($data['meta']['return_url']) && $data['meta']['return_url']) {
            return redirect()->to($data['meta']['return_url'] . '?auth_request=' . $request->input('token'));
        }

        return redirect()->route('index')->with('success', '登录请求已确认。');
    }

    public function fastLogin(string $token): RedirectResponse
    {
        $cache_key = 'session_login:' . $token;
        $user_id = Cache::get('session_login:' . $token);

        if (empty($user_id)) {
            return redirect()->route('index')->with('error', '登录请求的 Token 不存在或已过期。');
        }

        $user = User::find($user_id);

        if (empty($user)) {
            return redirect()->route('index')->with('error', '无法验证。');
        }

        Auth::guard('web')->login($user);

        Cache::forget($cache_key);

        return redirect()->route('index');
    }

    public function redirect(Request $request)
    {
        $request->session()->put('state', $state = Str::random(40));

        $query = http_build_query([
            'client_id' => config('oauth.client_id'),
            'redirect_uri' => config('oauth.callback_uri'),
            'response_type' => 'code',
            'scope' => config('oauth.scope'),
            'state' => $state,
        ]);

        return redirect()->to(config('oauth.oauth_auth_url') . '?' . $query);
    }

    public function callback(Request $request)
    {
        try {
            $authorize = Http::asForm()->throw()->post(config('oauth.oauth_token_url'), [
                'grant_type' => 'authorization_code',
                'client_id' => config('oauth.client_id'),
                'client_secret' => config('oauth.client_secret'),
                'redirect_uri' => config('oauth.callback_uri'),
                'code' => $request->input('code'),
            ])->getBody();
        } catch (Exception $e) {
            return redirect()->route('index')->with('error', '无法获取授权。' . $e->getMessage());
        }

        $authorize = json_decode($authorize);

        $http = Http::asForm()->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $authorize->access_token
        ])->throw();

        $oauth_user = $http->get(config('oauth.oauth_user_url'))->object();
        $real_name = $http->get(config('oauth.oauth_real_name_url'))->object();

        $user_sql = User::where('email', $oauth_user->email);
        $user = $user_sql->first();

        if (is_null($user)) {
            $name = $oauth_user->name;
            $email = $oauth_user->email;
            $email_verified_at = $oauth_user->email_verified_at;

            $user = new User();

            $user->name = $name;
            $user->email = $email;
            $user->email_verified_at = $email_verified_at;

            if ($real_name->real_name_verified_at) {
                $user->real_name_verified_at = $real_name->real_name_verified_at;
                $user->real_name = $real_name->real_name;
                $user->id_card = $real_name->id_card;
            }

            $user->password = Hash::make(Str::random(16));


            $user->save();

            $request->session()->put('auth.password_confirmed_at', time());
        } else {
            if ($user->name != $oauth_user->name) {
                User::where('email', $oauth_user->email)->update([
                    'name' => $oauth_user->name
                ]);
            }
        }

        Auth::guard('web')->loginUsingId($user->id, true);

        return redirect()->route('index');
    }

}
