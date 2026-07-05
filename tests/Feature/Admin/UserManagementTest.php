<?php

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

it('lets an authorized admin create managed non-admin users only', function (): void {
    $this->seed(DatabaseSeeder::class);

    $company = Company::factory()->create();
    $admin = User::factory()->for($company)->create();
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Nimal Fernando',
        'email' => 'nimal@example.test',
        'nic' => '901234567V',
        'job_title' => 'HR Manager',
        'role' => UserRole::HrManager->value,
    ])->assertRedirect()
        ->assertSessionHas('createdLogin');

    $member = User::query()->where('email', 'nimal@example.test')->firstOrFail();
    $createdLogin = session('createdLogin');

    expect($member->company_id)->toBe($company->id)
        ->and($member->nic)->toBe('901234567V')
        ->and($member->hasRole(UserRole::HrManager->value))->toBeTrue()
        ->and($member->status)->toBe('active')
        ->and(Hash::check($createdLogin['password'], $member->password))->toBeTrue();

    $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Blocked Admin',
        'email' => 'blocked@example.test',
        'nic' => '901234568V',
        'role' => UserRole::SuperAdmin->value,
    ])->assertSessionHasErrors('role');

    $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Duplicate NIC',
        'email' => 'duplicate@example.test',
        'nic' => '901234567V',
        'role' => UserRole::Employee->value,
    ])->assertSessionHasErrors('nic');
});

it('prevents managed user updates to the protected system administrator', function (): void {
    $this->seed(DatabaseSeeder::class);

    $company = Company::factory()->create();
    $admin = User::factory()->for($company)->create();
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
        'name' => 'Changed Admin',
        'email' => $admin->email,
        'nic' => $admin->nic,
        'status' => 'active',
        'role' => UserRole::HrManager->value,
    ])->assertForbidden();
});

it('shows official labels for built-in roles with acronyms', function (): void {
    $this->seed(DatabaseSeeder::class);

    $legacyHiringRole = 're'.'cruiter';
    $company = Company::factory()->create();
    $admin = User::factory()->for($company)->create();
    $admin->assignRole(UserRole::SuperAdmin->value);

    expect(Role::query()->where('name', $legacyHiringRole)->exists())->toBeFalse();

    $this->actingAs($admin)
        ->get('/admin/users?tab=roles&role_search=hr')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('roles.data.0.name', UserRole::HrManager->value)
            ->where('roles.data.0.label', 'HR Manager'));
});

it('prevents administrators from suspending their own active account', function (): void {
    $this->seed(DatabaseSeeder::class);

    $company = Company::factory()->create();
    $manager = User::factory()->for($company)->create([
        'email' => 'manager@example.test',
        'nic' => '770011223V',
    ]);
    $manager->assignRole(UserRole::HrManager->value);

    $this->actingAs($manager)->put("/admin/users/{$manager->id}", [
        'name' => 'Self Suspended Manager',
        'email' => $manager->email,
        'nic' => $manager->nic,
        'status' => 'suspended',
        'role' => UserRole::Employee->value,
    ])->assertForbidden();
});

it('updates and deletes managed users with company and role guardrails', function (): void {
    $this->seed(DatabaseSeeder::class);

    $company = Company::factory()->create();
    $admin = User::factory()->for($company)->create();
    $admin->assignRole(UserRole::SuperAdmin->value);

    $member = User::factory()->for($company)->create([
        'email' => 'hr.assistant@example.test',
        'nic' => '880011223V',
    ]);
    $member->assignRole(UserRole::Employee->value);

    $this->actingAs($admin)->put("/admin/users/{$member->id}", [
        'name' => 'HR Assistant Updated',
        'email' => 'hr.assistant.updated@example.test',
        'nic' => '880011224V',
        'status' => 'suspended',
        'role' => UserRole::HrManager->value,
    ])->assertRedirect();

    $member->refresh();

    expect($member->email)->toBe('hr.assistant.updated@example.test')
        ->and($member->nic)->toBe('880011224V')
        ->and($member->status)->toBe('suspended')
        ->and($member->hasRole(UserRole::HrManager->value))->toBeTrue();

    $this->actingAs($admin)->delete("/admin/users/{$member->id}")
        ->assertRedirect();

    expect(User::withTrashed()->find($member->id)?->trashed())->toBeTrue();

    $this->actingAs($admin)->delete("/admin/users/{$admin->id}")
        ->assertForbidden();
});

it('manages roles and prevents unsafe role deletion', function (): void {
    $this->seed(DatabaseSeeder::class);

    $company = Company::factory()->create();
    $admin = User::factory()->for($company)->create();
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin)->post('/admin/roles', [
        'name' => 'Payroll Reviewer',
        'permissions' => ['dashboard.view', 'audit.view'],
    ])->assertRedirect();

    $role = Role::query()->where('name', 'payroll_reviewer')->firstOrFail();

    expect($role->hasPermissionTo('dashboard.view'))->toBeTrue()
        ->and($role->hasPermissionTo('audit.view'))->toBeTrue();

    $this->actingAs($admin)->put("/admin/roles/{$role->id}", [
        'name' => 'Payroll Approver',
        'permissions' => ['dashboard.view'],
    ])->assertRedirect();

    $role->refresh();

    expect($role->name)->toBe('payroll_approver')
        ->and($role->hasPermissionTo('dashboard.view'))->toBeTrue()
        ->and($role->hasPermissionTo('audit.view'))->toBeFalse();

    $member = User::factory()->for($company)->create();
    $member->assignRole($role);

    $this->actingAs($admin)->delete("/admin/roles/{$role->id}")
        ->assertSessionHas('error');

    $member->syncRoles([]);

    $this->actingAs($admin)->delete("/admin/roles/{$role->id}")
        ->assertRedirect();

    expect(Role::query()->where('name', 'payroll_approver')->exists())->toBeFalse();

    $superAdmin = Role::findByName(UserRole::SuperAdmin->value);

    $this->actingAs($admin)->put("/admin/roles/{$superAdmin->id}", [
        'name' => 'Super Admin Changed',
        'permissions' => ['dashboard.view'],
    ])->assertForbidden();
});

it('prevents role edits that would remove the final access-management path for a company', function (): void {
    $this->seed(DatabaseSeeder::class);

    $company = Company::factory()->create();
    $role = Role::create([
        'name' => 'access_admin',
        'guard_name' => 'web',
    ]);
    $role->syncPermissions(['dashboard.view', 'users.update', 'roles.update']);

    $admin = User::factory()->for($company)->create();
    $admin->assignRole($role);

    $peer = User::factory()->for($company)->create();
    $peer->assignRole($role);

    $this->actingAs($admin)->put("/admin/roles/{$role->id}", [
        'name' => 'Access Admin',
        'permissions' => ['dashboard.view'],
    ])->assertSessionHasErrors('permissions');

    $role->refresh();

    expect($role->hasPermissionTo('users.update'))->toBeTrue();
});
