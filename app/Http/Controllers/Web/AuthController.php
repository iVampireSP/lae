<?php

namespace App\Http\Controllers\Web;

// use App\Helpers\ApiResponse;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\User\UserNotification;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function redirect(Request $request): RedirectResponse
    {
        $request->session()->put('state', $state = Str::random(40));

        $query = http_build_query([
            'client_id' => config('oauth.client_id'),
            'redirect_uri' => config('oauth.callback_uri'),
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
            'meta' => 'test_meta',
        ]);

        return redirect()->to(config('oauth.oauth_auth_url') . '?' . $query);
    }

    public function callback(Request $request): RedirectResponse
    {
        $state = $request->session()->pull('state');

        if (!strlen($state) > 0 && $state === $request->input('state')) {
            return redirect(route('login'));
        }

        $http = new Client();

        try {
            $authorize = $http->post(config('oauth.oauth_token_url'), [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => config('oauth.client_id'),
                    'client_secret' => config('oauth.client_secret'),
                    'redirect_uri' => config('oauth.callback_uri'),
                    'code' => $request->input('code'),
                ],
            ])->getBody();
        } catch (ClientException|GuzzleException) {
            return redirect(route('login'));
        }

        $authorize = json_decode($authorize);

        try {
            $oauth_user = $http->get(config('oauth.oauth_user_url'), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $authorize->access_token,
                ],
            ])->getBody();
        } catch (GuzzleException) {
            return redirect(route('login'));
        }
        $oauth_user = json_decode($oauth_user);


        $user_sql = (new User)->where('email', $oauth_user->email);
        $user = $user_sql->first();

        if (is_null($user)) {
            $name = $oauth_user->name;
            $email = $oauth_user->email;
            $email_verified_at = $oauth_user->email_verified_at ?? now();

            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->password = null;
            $user->email_verified_at = $email_verified_at;
            $user->save();

            $request->session()->put('auth.password_confirmed_at', time());
        } else {
            if ($user->name != $oauth_user->name) {
                (new User)->where('email', $oauth_user->email)->update([
                    'name' => $oauth_user->name
                ]);
            }
        }

        Auth::loginUsingId($user->id, true);

        return redirect()->route('index', ['callback' => session('callback')]);
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
