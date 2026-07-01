<?php

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

it('redirects first admin to company onboarding until required company details are completed', function (): void {
    $this->seed(DatabaseSeeder::class);

    $admin = User::factory()->create([
        'email' => 'admin@example.test',
        'password' => 'VerySecure!123',
    ]);
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->post('/login', [
        'email' => 'admin@example.test',
        'password' => 'VerySecure!123',
    ])->assertRedirect('/company/onboarding');

    $this->actingAs($admin)->get('/dashboard')->assertRedirect('/company/onboarding');
});

it('allows dashboard access after company onboarding', function (): void {
    $this->seed(DatabaseSeeder::class);

    $company = Company::factory()->create();
    $admin = User::factory()->for($company)->create();
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin)->get('/dashboard')->assertOk();
});
