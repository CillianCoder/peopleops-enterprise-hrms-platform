<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

final readonly class CreateManagedUser
{
    public function handle(User $actor, array $data): array
    {
        return DB::transaction(function () use ($actor, $data): array {
            if ($actor->company_id === null) {
                throw ValidationException::withMessages([
                    'company' => 'Complete company onboarding before adding users.',
                ]);
            }

            $temporaryPassword = str()->password(14, symbols: true, spaces: false);

            $user = User::query()->create([
                'company_id' => $actor->company_id,
                'name' => $data['name'],
                'email' => mb_strtolower($data['email']),
                'nic' => mb_strtoupper($data['nic']),
                'password' => $temporaryPassword,
                'job_title' => $data['job_title'] ?? null,
                'phone' => $data['phone'] ?? null,
                'status' => 'active',
            ]);

            Role::findOrCreate($data['role'], 'web');
            $user->assignRole($data['role']);

            activity('users')
                ->causedBy($actor)
                ->performedOn($user)
                ->event('managed_user_created')
                ->withProperties(['role' => $data['role']])
                ->log('User was created by an administrator.');

            return [
                'user' => $user,
                'temporary_password' => $temporaryPassword,
            ];
        });
    }
}
