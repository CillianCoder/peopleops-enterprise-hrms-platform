<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $company = $user?->company;

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'jobTitle' => $user->job_title,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->values(),
                    'companyReady' => $company?->onboarding_completed_at !== null,
                ] : null,
            ],
            'company' => $company ? [
                'id' => $company->id,
                'name' => $company->name,
                'logoPath' => $company->logo_path,
                'logoUrl' => $company->logo_path ? route('company.logo') : null,
            ] : null,
            'initialSetupOpen' => User::query()->doesntExist(),
            'roles' => collect(UserRole::cases())->map(fn (UserRole $role): array => [
                'value' => $role->value,
                'label' => $role->label(),
                'assignable' => $role !== UserRole::SuperAdmin,
            ])->values(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
