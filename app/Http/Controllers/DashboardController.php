<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

final class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $user = auth()->user();

        return Inertia::render('Dashboard/Admin', [
            'metrics' => [
                'activeUsers' => User::query()->where('company_id', $user?->company_id)->where('status', 'active')->count(),
                'invitedUsers' => User::query()->where('company_id', $user?->company_id)->where('status', 'invited')->count(),
                'suspendedUsers' => User::query()->where('company_id', $user?->company_id)->where('status', 'suspended')->count(),
                'rolesConfigured' => Role::query()->count(),
            ],
            'recentUsers' => User::query()
                ->with('roles')
                ->where('company_id', $user?->company_id)
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn (User $member): array => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'status' => $member->status,
                    'role' => $member->roles->first()?->name,
                    'createdAt' => $member->created_at?->toFormattedDateString(),
                ]),
        ]);
    }
}
