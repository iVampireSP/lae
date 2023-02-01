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
     *
     * @return View
     */
    public function showConfirmForm(): View
    {
        return view('auth.passwords.confirm');
    }

    /**
     * Confirm the given user's password.
     *
     * @param  Request  $request
     * @return RedirectResponse|JsonResponse
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
     * Reset the password confirmation timeout.
     *
     * @param  Request  $request
     * @return void
     */
    protected function resetPasswordConfirmationTimeout(Request $request): void
    {
        $request->session()->put('auth.password_confirmed_at', time());
    }

    /**
     * Get the password confirmation validation rules.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'password' => 'required|current_password:web',
        ];
    }

    /**
     * Get the password confirmation validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages(): array
    {
        return [];
    }
}
