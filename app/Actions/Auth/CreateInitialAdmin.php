<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

final readonly class CreateInitialAdmin
{
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            if (User::query()->lockForUpdate()->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'Initial setup has already been completed. Ask an administrator to create your account.',
                ]);
            }

            $user = User::query()->create([
                'name' => $data['name'],
                'email' => mb_strtolower($data['email']),
                'password' => $data['password'],
                'job_title' => 'System Administrator',
                'status' => 'active',
            ]);

            Role::findOrCreate(UserRole::SuperAdmin->value, 'web');
            $user->assignRole(UserRole::SuperAdmin->value);

            activity('security')
                ->causedBy($user)
                ->performedOn($user)
                ->event('initial_admin_created')
                ->log('Initial system administrator was created.');

            return $user;
        });
    }
}
