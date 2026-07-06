<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class AuthenticatedSessionController extends Controller
{
    public function create(): Response|RedirectResponse
    {
        if (User::query()->doesntExist()) {
            return redirect()->route('register');
        }

        return Inertia::render('Auth/Login', [
            'canResetPassword' => true,
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $request->user()?->forceFill(['last_login_at' => now()])->save();

        activity('security')
            ->causedBy($request->user())
            ->performedOn($request->user())
            ->event('login_succeeded')
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => str((string) $request->userAgent())->limit(180)->toString(),
            ])
            ->log('User signed in.');

        if ($request->user()?->company?->onboarding_completed_at === null && $request->user()?->can('company.update')) {
            return redirect()->intended(route('company.onboarding.edit'));
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        activity('security')
            ->causedBy($user)
            ->performedOn($user)
            ->event('logout_succeeded')
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => str((string) $request->userAgent())->limit(180)->toString(),
            ])
            ->log('User signed out.');

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
