<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Support\AccessControlSafety;

final class UserPolicy
{
    public function update(User $actor, User $target): bool
    {
        if (! $actor->can('users.update')) {
            return false;
        }

        if ($actor->is($target) || $target->isSuperAdmin()) {
            return false;
        }

        return $actor->company_id !== null && $actor->company_id === $target->company_id;
    }

    public function delete(User $actor, User $target): bool
    {
        if (! $actor->can('users.delete')) {
            return false;
        }

        if ($actor->is($target) || $target->isSuperAdmin()) {
            return false;
        }

        if ($actor->company_id === null || $actor->company_id !== $target->company_id) {
            return false;
        }

        return ! app(AccessControlSafety::class)->isLastActiveCriticalUser($target);
    }
}
