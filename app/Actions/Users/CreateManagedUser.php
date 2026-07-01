<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

final readonly class CreateManagedUser
{
    public function handle(User $actor, array $data): User
    {
        return DB::transaction(function () use ($actor, $data): User {
            if ($actor->company_id === null) {
                throw ValidationException::withMessages([
                    'company' => 'Complete company onboarding before adding users.',
                ]);
            }

            $user = User::query()->create([
                'company_id' => $actor->company_id,
                'name' => $data['name'],
                'email' => mb_strtolower($data['email']),
                'password' => str()->password(32),
                'job_title' => $data['job_title'] ?? null,
                'phone' => $data['phone'] ?? null,
                'status' => 'invited',
            ]);

            Role::findOrCreate($data['role'], 'web');
            $user->assignRole($data['role']);

            Password::sendResetLink(['email' => $user->email]);

            activity('users')
                ->causedBy($actor)
                ->performedOn($user)
                ->event('managed_user_created')
                ->withProperties(['role' => $data['role']])
                ->log('User was created by an administrator.');

            return $user;
        });
    }
}
