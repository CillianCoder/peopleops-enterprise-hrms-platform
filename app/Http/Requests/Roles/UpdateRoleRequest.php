<?php

declare(strict_types=1);

namespace App\Http\Requests\Roles;

use App\Support\AccessControlSafety;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Role|null $role */
        $role = $this->route('role');

        return $role instanceof Role && $this->user()?->can('update', $role) === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => str($this->string('name')->toString())->squish()->lower()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString(),
        ]);
    }

    public function rules(): array
    {
        /** @var Role $role */
        $role = $this->route('role');

        return [
            'name' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_]+$/', 'not_in:super_admin', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role)],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists(Permission::class, 'name')->where('guard_name', 'web')],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Role $role */
                $role = $this->route('role');
                $accessSafety = app(AccessControlSafety::class);
                $permissions = $this->input('permissions', []);

                if ($accessSafety->roleUpdateWouldLockOutCompany($role, is_array($permissions) ? $permissions : [])) {
                    $validator->errors()->add('permissions', 'At least one active access administrator must remain before removing access-management permissions from this role.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'name.not_in' => 'The protected super administrator role cannot be edited.',
            'name.regex' => 'Role names may only contain letters, numbers, and underscores.',
        ];
    }
}
