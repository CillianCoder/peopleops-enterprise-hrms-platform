<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

it('allows initial registration only when there are no users', function (): void {
    $this->seed(DatabaseSeeder::class);

    $this->post('/register', [
        'name' => 'Asha Perera',
        'email' => 'asha@example.test',
        'password' => 'VerySecure!123',
        'password_confirmation' => 'VerySecure!123',
    ])->assertRedirect('/company/onboarding');

    $user = User::query()->firstOrFail();

    expect($user->hasRole(UserRole::SuperAdmin->value))->toBeTrue()
        ->and(Hash::check('VerySecure!123', $user->password))->toBeTrue();

    Auth::logout();

    $this->post('/register', [
        'name' => 'Second User',
        'email' => 'second@example.test',
        'password' => 'VerySecure!123',
        'password_confirmation' => 'VerySecure!123',
    ])->assertForbidden();
});
