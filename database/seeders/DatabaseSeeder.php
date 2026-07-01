<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'company.update',
            'users.view',
            'users.create',
            'users.update',
            'users.deactivate',
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (UserRole::cases() as $role) {
            Role::findOrCreate($role->value, 'web');
        }

        Role::findByName(UserRole::SuperAdmin->value)
            ->syncPermissions($permissions);

        Role::findByName(UserRole::HrManager->value)
            ->syncPermissions(['dashboard.view', 'users.view', 'users.create', 'users.update']);

        Role::findByName(UserRole::DepartmentManager->value)
            ->syncPermissions(['dashboard.view']);

        Role::findByName(UserRole::Recruiter->value)
            ->syncPermissions(['dashboard.view']);

        Role::findByName(UserRole::FinanceOfficer->value)
            ->syncPermissions(['dashboard.view']);

        Role::findByName(UserRole::Auditor->value)
            ->syncPermissions(['dashboard.view', 'audit.view']);

        Role::findByName(UserRole::Employee->value)
            ->syncPermissions(['dashboard.view']);
    }
}
