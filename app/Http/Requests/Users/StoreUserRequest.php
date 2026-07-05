<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

final class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.create') === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'lowercase', 'email:rfc', 'max:255', 'unique:users,email'],
            'nic' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9\\-\\/]+$/', 'unique:users,nic'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => [
                'required',
                Rule::exists(Role::class, 'name')->where('guard_name', 'web'),
                Rule::notIn(['super_admin']),
            ],
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
