<?php

namespace App\Helpers\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

trait ThrottlesLogins
{
    /**
     * Determine if the user has too many failed login attempts.
     *
     * @param  Request  $request
     * @return bool
     */
    protected function hasTooManyLoginAttempts(Request $request): bool
    {
        return $this->limiter()->tooManyAttempts(
            $this->throttleKey($request), $this->maxAttempts()
        );
    }

    /**
     * Get the rate limiter instance.
     *
     * @return RateLimiter
     */
    protected function limiter(): RateLimiter
    {
        return app(RateLimiter::class);
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param  Request  $request
     * @return string
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input($this->username())).'|'.$request->ip());
    }

    /**
     * Get the maximum number of attempts to allow.
     *
     * @return int
     */
    public function maxAttempts(): int
    {
        return property_exists($this, 'maxAttempts') ? $this->maxAttempts : 5;
    }

    /**
     * Increment the login attempts for the user.
     *
     * @param  Request  $request
     * @return void
     */
    protected function incrementLoginAttempts(Request $request): void
    {
        $this->limiter()->hit(
            $this->throttleKey($request), $this->decayMinutes() * 60
        );
    }

    /**
     * Get the number of minutes to throttle for.
     *
     * @return int
     */
    public function decayMinutes(): int
    {
        return property_exists($this, 'decayMinutes') ? $this->decayMinutes : 1;
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  Request  $request
     * @return ResponseAlias
     *
     * @throws ValidationException
     */
    protected function sendLockoutResponse(Request $request): ResponseAlias
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ])],
        ])->status(ResponseAlias::HTTP_TOO_MANY_REQUESTS);
    }

    /**
     * Clear the login locks for the given user credentials.
     *
     * @param  Request  $request
     * @return void
     */
    protected function clearLoginAttempts(Request $request): void
    {
        $this->limiter()->clear($this->throttleKey($request));
    }

    /**
     * Fire an event when a lockout occurs.
     *
     * @param  Request  $request
     * @return void
     */
    protected function fireLockoutEvent(Request $request): void
    {
        event(new Lockout($request));
    }
}
