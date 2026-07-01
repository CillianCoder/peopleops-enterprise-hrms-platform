<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreateInitialAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\InitialRegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class RegisteredUserController extends Controller
{
    public function create(): Response|RedirectResponse
    {
        if (User::query()->exists()) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/InitialRegister');
    }

    public function store(InitialRegisterRequest $request, CreateInitialAdmin $createInitialAdmin): RedirectResponse
    {
        $user = $createInitialAdmin->handle($request->validated());

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('company.onboarding.edit');
    }
}
