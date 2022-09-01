<?php

namespace App\Http\Controllers;

// use App\Helpers\ApiResponse;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // use ApiResponse;

    public function index(Request $request)
    {

        // test
        // $handle = new \App\Jobs\Remote\PushHost();
        // $handle->handle();

        // $handle = new \App\Jobs\Remote\PushWorkOrder();
        // $handle->handle();

        // if logged in
        if (Auth::check()) {
            $token_name = 'login token ' . now()->toDateString();

            $token = $request->user()->createToken($token_name, ['user:login'])->plainTextToken;

            if ($request->callback) {
                return redirect($request->callback . '?token=' . $token);
            } else if ($request->getToken) {
                return $this->created($token);
            } else {
                return view('index');
            }
        } else {
            // save callback url and referer url to session
            session(['callback' => $request->callback]);

            return redirect()->route('login');
        }
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

        $user_sql = User::where('email', $oauth_user->email);
        $user = $user_sql->first();

        if (is_null($user)) {
            $name = $oauth_user->name;
            $email = $oauth_user->email;
            $email_verified_at = $oauth_user->email_verified_at;
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => null,
                'email_verified_at' => $email_verified_at,
                'oauth_id' => $oauth_user->id,
                'provider' => 'LoliArt',
                'provider_id' => $oauth_user->id,
                'real_name' => $oauth_user->real_name,
            ]);

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

    public function reset()
    {
        return view('password.reset');
    }

    public function setup_password(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        User::find(auth()->id())->update(['password' => Hash::make($request->password)]);

        return redirect()->route('main.index');
    }

    public function confirm()
    {
        return view('password.confirm');
    }

    public function confirm_password(Request $request)
    {
        $request->validate($this->password_rules());

        $request->session()->put('auth.password_confirmed_at', time());

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->intended($this->redirectPath());
    }

    protected function password_rules()
    {
        return [
            'password' => 'required|password',
        ];
    }

    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/';
    }

    public function createApiToken(Request $request)
    {
        $request->validate([
            'name' => 'required|max:30',
        ]);
        $user = $request->user();
        $token = $user->createToken($request->name, ['user:login'])->plainTextToken;

        return back()->with('token', $token);
    }

    public function invokeAllApiToken(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        return back()->with('success', 'OK');
    }

    public function logout()
    {
        Auth::logout();
        // session()->destroy();
        return redirect()->route('index');
    }
}
