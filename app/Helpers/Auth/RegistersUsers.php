<?php

namespace App\Helpers\Auth;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

trait RegistersUsers
{
    use RedirectsUsers;

    /**
     * Show the application registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     */
    public function register(Request $request): JsonResponse|RedirectResponse
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        $this->guard()->login($user);

        // if ($response = $this->registered($request, $user)) {
        //     return $response;
        // }

        session()->forget('affiliate_id');

        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect($this->redirectPath());
    }

    /**
     * Get the guard to be used during registration.
     */
    protected function guard(): StatefulGuard
    {
        return Auth::guard();
    }

    /**
     * The user has been registered.
     */
    protected function registered(Request $request, mixed $user): void
    {
        //
    }
}
