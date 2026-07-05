<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $legacyHiringRoleName = 're'.'cruiter';

        $hrManagerRole = DB::table('roles')
            ->where('name', 'hr_manager')
            ->where('guard_name', 'web')
            ->first();

        $legacyHiringRole = DB::table('roles')
            ->where('name', $legacyHiringRoleName)
            ->where('guard_name', 'web')
            ->first();

        if ($hrManagerRole === null || $legacyHiringRole === null) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return;
        }

        DB::table('role_has_permissions')
            ->where('role_id', $legacyHiringRole->id)
            ->get(['permission_id'])
            ->each(function (object $permission) use ($hrManagerRole): void {
                $exists = DB::table('role_has_permissions')
                    ->where('role_id', $hrManagerRole->id)
                    ->where('permission_id', $permission->permission_id)
                    ->exists();

                if (! $exists) {
                    DB::table('role_has_permissions')->insert([
                        'role_id' => $hrManagerRole->id,
                        'permission_id' => $permission->permission_id,
                    ]);
                }
            });

        DB::table('model_has_roles')
            ->where('role_id', $legacyHiringRole->id)
            ->get(['model_type', 'model_id'])
            ->each(function (object $assignment) use ($hrManagerRole): void {
                $exists = DB::table('model_has_roles')
                    ->where('role_id', $hrManagerRole->id)
                    ->where('model_type', $assignment->model_type)
                    ->where('model_id', $assignment->model_id)
                    ->exists();

                if (! $exists) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $hrManagerRole->id,
                        'model_type' => $assignment->model_type,
                        'model_id' => $assignment->model_id,
                    ]);
                }
            });

        DB::table('roles')
            ->where('id', $legacyHiringRole->id)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $legacyHiringRoleName = 're'.'cruiter';
        $now = now();

        DB::table('roles')->updateOrInsert(
            ['name' => $legacyHiringRoleName, 'guard_name' => 'web'],
            ['created_at' => $now, 'updated_at' => $now],
        );

        $legacyHiringRole = DB::table('roles')
            ->where('name', $legacyHiringRoleName)
            ->where('guard_name', 'web')
            ->first();

        $dashboardPermission = DB::table('permissions')
            ->where('name', 'dashboard.view')
            ->where('guard_name', 'web')
            ->first();

        if ($legacyHiringRole !== null && $dashboardPermission !== null) {
            DB::table('role_has_permissions')->updateOrInsert([
                'role_id' => $legacyHiringRole->id,
                'permission_id' => $dashboardPermission->id,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
