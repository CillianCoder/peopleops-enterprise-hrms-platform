<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('validates and stores company onboarding details with private logo storage', function (): void {
    $this->seed(DatabaseSeeder::class);
    Storage::fake('local');

    config(['filesystems.default' => 'local']);

    $admin = User::factory()->create();
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin)->put('/company/onboarding', [
        'name' => 'Northstar People',
        'legal_name' => 'Northstar People Pvt Ltd',
        'industry' => 'software',
        'email' => 'hello@northstar.test',
        'phone' => '+94112345678',
        'address_line_1' => '14 Lake Drive',
        'city' => 'Colombo',
        'country' => 'LK',
        'timezone' => 'Asia/Colombo',
        'currency' => 'LKR',
        'logo' => UploadedFile::fake()->image('logo.png', 500, 500),
    ])->assertRedirect('/dashboard');

    $admin->refresh();

    expect($admin->company)->not->toBeNull()
        ->and($admin->company->onboarding_completed_at)->not->toBeNull()
        ->and($admin->company->logo_path)->toStartWith('company-logos/');

    Storage::disk('local')->assertExists($admin->company->logo_path);
});
