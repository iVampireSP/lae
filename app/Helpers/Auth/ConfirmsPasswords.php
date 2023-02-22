<?php

namespace App\Helpers\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

trait ConfirmsPasswords
{
    use RedirectsUsers;

    /**
     * Display the password confirmation view.
     */
    public function showConfirmForm(): View
    {
        return view('auth.passwords.confirm');
    }

    /**
     * Confirm the given user's password.
     */
    public function confirm(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate($this->rules(), $this->validationErrorMessages());

        $this->resetPasswordConfirmationTimeout($request);

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->intended($this->redirectPath());
    }

    /**
     * Get the password confirmation validation rules.
     */
    protected function rules(): array
    {
        return [
            'password' => 'required|current_password:web',
        ];
    }

    /**
     * Get the password confirmation validation error messages.
     */
    protected function validationErrorMessages(): array
    {
        return [];
    }

    /**
     * Reset the password confirmation timeout.
     */
    protected function resetPasswordConfirmationTimeout(Request $request): void
    {
        $request->session()->put('auth.password_confirmed_at', time());
    }
}
