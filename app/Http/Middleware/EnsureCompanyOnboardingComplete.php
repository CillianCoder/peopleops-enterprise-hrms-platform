<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureCompanyOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->can('company.update') && $user->company?->onboarding_completed_at === null) {
            return redirect()->route('company.onboarding.edit');
        }

        if ($user->company?->onboarding_completed_at === null) {
            abort(403, 'Company setup is not complete yet. Ask your administrator to finish onboarding.');
        }

        return $next($request);
    }
}
