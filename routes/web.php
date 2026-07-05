<?php

use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CompanyLogoController;
use App\Http\Controllers\CompanyOnboardingController;
use App\Http\Controllers\DashboardController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return User::query()->exists()
        ? redirect()->route('login')
        : redirect()->route('register');
});

Route::middleware('guest')->group(function (): void {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware(['auth', 'active.account'])->group(function (): void {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('email/verify', fn () => redirect()->route('dashboard'))
        ->name('verification.notice');

    Route::get('email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('dashboard')->with('success', 'Email address verified.');
    })->middleware('signed')->name('verification.verify');

    Route::post('email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent.');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::get('company/logo', CompanyLogoController::class)->name('company.logo');

    Route::get('company/onboarding', [CompanyOnboardingController::class, 'edit'])
        ->middleware('permission:company.update')
        ->name('company.onboarding.edit');
    Route::put('company/onboarding', [CompanyOnboardingController::class, 'update'])
        ->middleware('permission:company.update')
        ->name('company.onboarding.update');

    Route::middleware('company.ready')->group(function (): void {
        Route::get('dashboard', DashboardController::class)
            ->middleware('permission:dashboard.view')
            ->name('dashboard');

        Route::get('admin/users', [UserManagementController::class, 'index'])
            ->middleware('permission:users.view')
            ->name('admin.users.index');
        Route::post('admin/users', [UserManagementController::class, 'store'])
            ->middleware('permission:users.create')
            ->name('admin.users.store');
        Route::put('admin/users/{user}', [UserManagementController::class, 'update'])
            ->middleware('permission:users.update')
            ->name('admin.users.update');
        Route::delete('admin/users/{user}', [UserManagementController::class, 'destroy'])
            ->middleware('permission:users.delete')
            ->name('admin.users.destroy');

        Route::post('admin/roles', [RoleManagementController::class, 'store'])
            ->middleware('permission:roles.create')
            ->name('admin.roles.store');
        Route::put('admin/roles/{role}', [RoleManagementController::class, 'update'])
            ->middleware('permission:roles.update')
            ->name('admin.roles.update');
        Route::delete('admin/roles/{role}', [RoleManagementController::class, 'destroy'])
            ->middleware('permission:roles.delete')
            ->name('admin.roles.destroy');
    });
});
