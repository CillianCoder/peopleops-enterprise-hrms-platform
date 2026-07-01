<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserPolicy
{
    public function update(User $actor, User $target): bool
    {
        if (! $actor->can('users.update')) {
            return false;
        }

        if ($target->isSuperAdmin()) {
            return false;
        }

        return $actor->company_id !== null && $actor->company_id === $target->company_id;
    }
}
