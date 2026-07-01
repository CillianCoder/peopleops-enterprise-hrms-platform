<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Users\CreateManagedUser;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::query()
            ->with('roles')
            ->where('company_id', $request->user()?->company_id)
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%")
                        ->orWhere('job_title', 'ilike', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'jobTitle' => $user->job_title,
                'phone' => $user->phone,
                'status' => $user->status,
                'role' => $user->roles->first()?->name,
                'isSuperAdmin' => $user->isSuperAdmin(),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only('search'),
            'assignableRoles' => collect(UserRole::cases())
                ->filter(fn (UserRole $role): bool => $role !== UserRole::SuperAdmin)
                ->map(fn (UserRole $role): array => ['value' => $role->value, 'label' => $role->label()])
                ->values(),
        ]);
    }

    public function store(StoreUserRequest $request, CreateManagedUser $createManagedUser): RedirectResponse
    {
        $createManagedUser->handle($request->user(), $request->validated());

        return back()->with('success', 'User created and password setup email sent.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'job_title' => $data['job_title'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
        ]);

        $user->syncRoles([$data['role']]);

        activity('users')
            ->causedBy($request->user())
            ->performedOn($user)
            ->event('managed_user_updated')
            ->withProperties(['role' => $data['role'], 'status' => $data['status']])
            ->log('User account was updated by an administrator.');

        return back()->with('success', 'User account updated.');
    }
}
