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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = min(max((int) $request->integer('per_page', 10), 5), 25);
        $status = $request->string('status')->toString();
        $role = $request->string('role')->toString();
        $search = trim($request->string('search')->toString());
        $roleSearch = trim($request->string('role_search')->toString());

        $users = User::query()
            ->with('roles')
            ->where('company_id', $request->user()?->company_id)
            ->when($search !== '', function ($query) use ($search): void {
                $needle = mb_strtolower($search);

                $query->where(function ($query) use ($needle): void {
                    $query->whereRaw('lower(name) like ?', ["%{$needle}%"])
                        ->orWhereRaw('lower(email) like ?', ["%{$needle}%"])
                        ->orWhereRaw('lower(coalesce(nic, \'\')) like ?', ["%{$needle}%"])
                        ->orWhereRaw('lower(coalesce(job_title, \'\')) like ?', ["%{$needle}%"]);
                });
            })
            ->when(in_array($status, ['active', 'suspended'], true), fn ($query) => $query->where('status', $status))
            ->when($role !== '', fn ($query) => $query->role($role))
            ->latest()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nic' => $user->nic,
                'jobTitle' => $user->job_title,
                'phone' => $user->phone,
                'status' => $user->status,
                'role' => $user->roles->first()?->name,
                'roleLabel' => $this->roleLabel($user->roles->first()?->name),
                'isSuperAdmin' => $user->isSuperAdmin(),
                'canEdit' => $request->user()?->can('update', $user) === true,
                'canDelete' => $request->user()?->can('delete', $user) === true,
                'createdAt' => $user->created_at?->toDateString(),
            ]);

        $roles = Role::query()
            ->withCount('users')
            ->with('permissions:id,name')
            ->when($roleSearch !== '', function ($query) use ($roleSearch): void {
                $needle = mb_strtolower($roleSearch);

                $query->whereRaw('lower(name) like ?', ["%{$needle}%"]);
            })
            ->orderByRaw("case when name = 'super_admin' then 0 else 1 end")
            ->orderBy('name')
            ->paginate(8, ['*'], 'roles_page')
            ->withQueryString()
            ->through(fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'label' => $this->roleLabel($role->name),
                'usersCount' => $role->users_count,
                'permissions' => $role->permissions->pluck('name')->sort()->values(),
                'protected' => $role->name === 'super_admin',
                'canEdit' => $request->user()?->can('update', $role) === true,
                'canDelete' => $request->user()?->can('delete', $role) === true && $role->users_count === 0,
            ]);

        $assignableRoles = Role::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'super_admin')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role): array => ['value' => $role->name, 'label' => $this->roleLabel($role->name)])
            ->values();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'role' => $role,
                'per_page' => $perPage,
                'role_search' => $roleSearch,
                'tab' => $request->string('tab')->toString() ?: 'users',
            ],
            'assignableRoles' => $assignableRoles,
            'statusOptions' => [
                ['value' => '', 'label' => 'All statuses'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'suspended', 'label' => 'Suspended'],
            ],
            'permissionGroups' => Permission::query()
                ->orderBy('name')
                ->get(['name'])
                ->groupBy(fn (Permission $permission): string => str($permission->name)->before('.')->headline()->toString())
                ->map(fn ($permissions, string $group): array => [
                    'group' => $group,
                    'permissions' => $permissions->map(fn (Permission $permission): array => [
                        'value' => $permission->name,
                        'label' => str($permission->name)->after('.')->replace('_', ' ')->headline()->toString(),
                    ])->values(),
                ])
                ->values(),
        ]);
    }

    public function store(StoreUserRequest $request, CreateManagedUser $createManagedUser): RedirectResponse
    {
        $result = $createManagedUser->handle($request->user(), $request->validated());
        /** @var User $user */
        $user = $result['user'];

        return back()
            ->with('success', 'User login created. Copy the temporary password before closing the panel.')
            ->with('createdLogin', [
                'name' => $user->name,
                'email' => $user->email,
                'password' => $result['temporary_password'],
            ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $wasActive = $user->status === 'active';

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'nic' => mb_strtoupper($data['nic']),
            'job_title' => $data['job_title'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
        ]);

        $user->syncRoles([$data['role']]);

        if ($wasActive && $user->status === 'suspended') {
            $this->invalidateUserSessions($user);
        }

        activity('users')
            ->causedBy($request->user())
            ->performedOn($user)
            ->event('managed_user_updated')
            ->withProperties(['role' => $data['role'], 'status' => $data['status']])
            ->log('User account was updated by an administrator.');

        return back()->with('success', 'User account updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->can('delete', $user), 403);

        $user->delete();

        activity('users')
            ->causedBy($request->user())
            ->performedOn($user)
            ->event('managed_user_deleted')
            ->log('User account was deleted by an administrator.');

        return back()->with('success', 'User account deleted.');
    }

    private function roleLabel(?string $role): ?string
    {
        if ($role === null) {
            return null;
        }

        $systemRole = UserRole::tryFrom($role);

        if ($systemRole !== null) {
            return $systemRole->label();
        }

        return str($role)->replace('_', ' ')->headline()->toString();
    }

    private function invalidateUserSessions(User $user): void
    {
        if (config('session.driver') !== 'database' || ! Schema::hasTable('sessions')) {
            return;
        }

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();
    }
}
