<?php

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Notification;

it('lets an authorized admin create managed non-admin users only', function (): void {
    Notification::fake();
    $this->seed(DatabaseSeeder::class);

    $company = Company::factory()->create();
    $admin = User::factory()->for($company)->create();
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Nimal Fernando',
        'email' => 'nimal@example.test',
        'job_title' => 'HR Manager',
        'role' => UserRole::HrManager->value,
    ])->assertRedirect();

    $member = User::query()->where('email', 'nimal@example.test')->firstOrFail();

    expect($member->company_id)->toBe($company->id)
        ->and($member->hasRole(UserRole::HrManager->value))->toBeTrue()
        ->and($member->status)->toBe('invited');

    $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Blocked Admin',
        'email' => 'blocked@example.test',
        'role' => UserRole::SuperAdmin->value,
    ])->assertSessionHasErrors('role');
});

it('prevents managed user updates to the protected system administrator', function (): void {
    $this->seed(DatabaseSeeder::class);

    $company = Company::factory()->create();
    $admin = User::factory()->for($company)->create();
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
        'name' => 'Changed Admin',
        'email' => $admin->email,
        'status' => 'active',
        'role' => UserRole::HrManager->value,
    ])->assertForbidden();
});
