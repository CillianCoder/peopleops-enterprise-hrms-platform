<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            activity('security')
                ->event('login_failed')
                ->withProperties([
                    'attempted_email' => mb_strtolower($this->string('email')->toString()),
                    'ip' => $this->ip(),
                    'user_agent' => str((string) $this->userAgent())->limit(180)->toString(),
                ])
                ->log('Sign-in failed.');

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if (Auth::user()?->status !== 'active' && Auth::user()?->status !== 'invited') {
            $user = Auth::user();

            Auth::logout();

            activity('security')
                ->causedBy($user)
                ->performedOn($user)
                ->event('login_blocked')
                ->withProperties([
                    'status' => $user?->status,
                    'ip' => $this->ip(),
                    'user_agent' => str((string) $this->userAgent())->limit(180)->toString(),
                ])
                ->log('Sign-in was blocked because the account is not active.');

            throw ValidationException::withMessages([
                'email' => 'This account is not active. Contact your PeopleOps administrator.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    private function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')->toString()).'|'.$this->ip());
    }
}
