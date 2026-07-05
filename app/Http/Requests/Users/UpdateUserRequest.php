<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Models\User;
use App\Support\AccessControlSafety;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Spatie\Permission\Models\Role;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $target */
        $target = $this->route('user');

        return $target instanceof User && $this->user()?->can('update', $target) === true;
    }

    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'lowercase', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'nic' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9\\-\\/]+$/', Rule::unique('users', 'nic')->ignore($user)],
            'job_title' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'status' => ['required', Rule::in(['active', 'suspended'])],
            'role' => [
                'required',
                Rule::exists(Role::class, 'name')->where('guard_name', 'web'),
                Rule::notIn(['super_admin']),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var User $user */
                $user = $this->route('user');
                $accessSafety = app(AccessControlSafety::class);

                if (! $accessSafety->isLastActiveCriticalUser($user)) {
                    return;
                }

                if ($this->string('status')->toString() === 'suspended') {
                    $validator->errors()->add('status', 'At least one active access administrator must remain before this user can be suspended.');
                }

                $role = Role::query()
                    ->where('guard_name', 'web')
                    ->where('name', $this->string('role')->toString())
                    ->with('permissions:id,name')
                    ->first();

                $permissions = $role?->permissions->pluck('name')->all() ?? [];

                if ($role !== null && $accessSafety->roleUpdateWouldRemoveContinuity($permissions) && $role->name !== 'super_admin') {
                    $validator->errors()->add('role', 'At least one active access administrator must remain before this role can be removed from the user.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'nic.regex' => 'The NIC may only contain letters, numbers, hyphens, and slashes.',
            'role.not_in' => 'The protected super administrator role cannot be assigned here.',
        ];
    }
}
