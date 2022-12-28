<?php

namespace App\Http\Controllers\Web;

// use App\Helpers\ApiResponse;

use App\Http\Controllers\Controller;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use function back;
use function config;
use function redirect;
use function session;
use function view;

class AuthController extends Controller
{
    // use ApiResponse;

    public function index(Request $request)
    {
        // if logged in
        if ($request->callback) {

            session(['callback' => $request->callback]);


            if (Auth::check()) {
                return redirect()->route('confirm_redirect');
            } else {
                return redirect()->route('login');
            }
        }

        return view('index');
    }

    public function confirm_redirect(Request $request)
    {
        // create token

        $callback = $request->callback ?? session('callback');


        return view('confirm_redirect', compact('callback'));
    }

    public function redirect(Request $request)
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

        // dd($query);

        return redirect()->to(config('oauth.oauth_auth_url') . '?' . $query);
    }

    public function callback(Request $request)
    {
        $state = $request->session()->pull('state');

        if (!strlen($state) > 0 && $state === $request->state) {
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
                    'code' => $request->code,
                ],
            ])->getBody();
        } catch (ClientException) {
            return redirect(route('login'));
        }

        $authorize = json_decode($authorize);

        $oauth_user = $http->get(config('oauth.oauth_user_url'), [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $authorize->access_token,
            ],
        ])->getBody();
        $oauth_user = json_decode($oauth_user);

        if (is_null($oauth_user->verified_at)) {
            return redirect()->route('not_verified');
        }

        $user_sql = User::where('email', $oauth_user->email);
        $user = $user_sql->first();

        if (is_null($user)) {
            $name = $oauth_user->name;
            $email = $oauth_user->email;
            $email_verified_at = $oauth_user->email_verified_at;

            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->password = null;
            $user->email_verified_at = $email_verified_at;
            $user->real_name = $oauth_user->real_name;
            $user->birthday_at = $oauth_user->birthday;
            $user->save();

            $request->session()->put('auth.password_confirmed_at', time());
        } else {
            if ($user->name != $oauth_user->name) {
                User::where('email', $oauth_user->email)->update([
                    'name' => $oauth_user->name
                ]);
            }
            // $api_token = $user->api_token;
        }

        Auth::loginUsingId($user->id, true);

        return redirect()->route('index', ['callback' => session('callback')]);
    }

    public function newToken(Request $request)
    {
        $request->validate([
            'token_name' => 'required|string|max:255',
        ]);

        $token = $request->user()->createToken($request->token_name);

        return back()->with('token', $token->plainTextToken);
    }

    public function deleteAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return back()->with('success', '所有 Token 删除成功。');
    }

    public function logout()
    {
        Auth::guard('web')->logout();

        session()->regenerateToken();

        return redirect()->route('index');
    }
}
