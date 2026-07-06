<?php

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Spatie\Activitylog\Models\Activity;

it('shows readable company-scoped audit logs to authorized administrators', function (): void {
    $this->seed(DatabaseSeeder::class);

    $company = Company::factory()->create();
    $admin = User::factory()->for($company)->create();
    $admin->assignRole(UserRole::SuperAdmin->value);

    $otherCompany = Company::factory()->create(['name' => 'Other Company']);
    $otherAdmin = User::factory()->for($otherCompany)->create();
    $otherAdmin->assignRole(UserRole::SuperAdmin->value);

    activity('users')
        ->causedBy($otherAdmin)
        ->performedOn($otherAdmin)
        ->event('managed_user_updated')
        ->log('Other company user was updated.');

    $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Audit Visible User',
        'email' => 'audit.visible@example.test',
        'nic' => '900000111V',
        'role' => UserRole::Employee->value,
    ])->assertRedirect();

    $this->actingAs($admin)
        ->get('/admin/audit?module=users&search=created')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('activities.data.0.module', 'Users')
            ->where('activities.data.0.eventLabel', 'User login created')
            ->where('activities.data.0.actor.email', $admin->email)
            ->where('activities.data.0.subject.detail', 'audit.visible@example.test')
            ->where('filters.module', 'users'));

    expect(Activity::query()->where('log_name', 'requests')->where('event', 'request_completed')->exists())->toBeTrue();
});

it('denies audit logs to users without audit permission', function (): void {
    $this->seed(DatabaseSeeder::class);

    $company = Company::factory()->create();
    $employee = User::factory()->for($company)->create();
    $employee->assignRole(UserRole::Employee->value);

    $this->actingAs($employee)
        ->get('/admin/audit')
        ->assertForbidden();
});

it('records rejected authenticated write attempts', function (): void {
    $this->seed(DatabaseSeeder::class);

    $company = Company::factory()->create();
    $admin = User::factory()->for($company)->create();
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Invalid Role User',
        'email' => 'invalid.role@example.test',
        'nic' => '900000112V',
        'role' => UserRole::SuperAdmin->value,
    ])->assertSessionHasErrors('role');

    $requestLog = Activity::query()
        ->where('log_name', 'requests')
        ->where('event', 'request_completed')
        ->latest('id')
        ->firstOrFail();

    expect($requestLog->properties->get('status_code'))->toBe(302)
        ->and($requestLog->properties->get('validation_errors'))->toBeTrue()
        ->and($requestLog->properties->get('route_name'))->toBe('admin.users.store');
});
