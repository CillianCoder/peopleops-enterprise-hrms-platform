<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Role;

final class AccessControlSafety
{
    private const CONTINUITY_PERMISSIONS = [
        'users.update',
        'roles.update',
    ];

    public function hasCriticalAccess(User $user): bool
    {
        if (! $user->exists || $user->status !== 'active') {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        foreach (self::CONTINUITY_PERMISSIONS as $permission) {
            if ($user->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    public function isLastActiveCriticalUser(User $user): bool
    {
        if ($user->company_id === null || ! $this->hasCriticalAccess($user)) {
            return false;
        }

        return ! User::query()
            ->where('company_id', $user->company_id)
            ->whereKeyNot($user->getKey())
            ->where('status', 'active')
            ->with('roles.permissions')
            ->get()
            ->contains(fn (User $candidate): bool => $this->hasCriticalAccess($candidate));
    }

    public function roleUpdateWouldRemoveContinuity(array $permissions): bool
    {
        return collect(self::CONTINUITY_PERMISSIONS)
            ->intersect($permissions)
            ->isEmpty();
    }

    public function roleUpdateWouldLockOutCompany(Role $role, array $permissions): bool
    {
        if (! $this->roleUpdateWouldRemoveContinuity($permissions)) {
            return false;
        }

        $companyIds = User::query()
            ->where('status', 'active')
            ->whereNotNull('company_id')
            ->role($role->name)
            ->distinct()
            ->pluck('company_id');

        foreach ($companyIds as $companyId) {
            $hasRemainingCriticalUser = User::query()
                ->where('company_id', $companyId)
                ->where('status', 'active')
                ->with(['permissions:id,name', 'roles.permissions'])
                ->get()
                ->contains(fn (User $user): bool => $this->hasCriticalAccessAfterRoleUpdate($user, $role, $permissions));

            if (! $hasRemainingCriticalUser) {
                return true;
            }
        }

        return false;
    }

    private function hasCriticalAccessAfterRoleUpdate(User $user, Role $changedRole, array $changedPermissions): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $directPermissions = $user->permissions->pluck('name');

        if ($directPermissions->intersect(self::CONTINUITY_PERMISSIONS)->isNotEmpty()) {
            return true;
        }

        foreach ($user->roles as $role) {
            $permissionNames = $role->is($changedRole)
                ? collect($changedPermissions)
                : $role->permissions->pluck('name');

            if ($permissionNames->intersect(self::CONTINUITY_PERMISSIONS)->isNotEmpty()) {
                return true;
            }
        }

        return false;
    }
}
