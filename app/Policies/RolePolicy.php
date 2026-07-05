<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

final class RolePolicy
{
    public function update(User $actor, Role $role): bool
    {
        return $actor->can('roles.update') && $role->name !== 'super_admin';
    }

    public function delete(User $actor, Role $role): bool
    {
        return $actor->can('roles.delete') && $role->name !== 'super_admin';
    }
}
