<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

final class RoleManagementController extends Controller
{
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        activity('roles')
            ->causedBy($request->user())
            ->performedOn($role)
            ->event('role_created')
            ->withProperties(['permissions' => $data['permissions'] ?? []])
            ->log('Role was created by an administrator.');

        return back()->with('success', 'Role created.');
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        activity('roles')
            ->causedBy($request->user())
            ->performedOn($role)
            ->event('role_updated')
            ->withProperties(['permissions' => $data['permissions'] ?? []])
            ->log('Role was updated by an administrator.');

        return back()->with('success', 'Role updated.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        abort_unless($request->user()?->can('delete', $role), 403);

        if ($role->users()->exists()) {
            return back()->with('error', 'Role cannot be deleted while users are assigned to it.');
        }

        $role->delete();

        activity('roles')
            ->causedBy($request->user())
            ->performedOn($role)
            ->event('role_deleted')
            ->log('Role was deleted by an administrator.');

        return back()->with('success', 'Role deleted.');
    }
}
