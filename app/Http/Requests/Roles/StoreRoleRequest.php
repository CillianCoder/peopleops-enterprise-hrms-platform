<?php

declare(strict_types=1);

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

final class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('roles.create') === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => str($this->string('name')->toString())->squish()->lower()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString(),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_]+$/', 'not_in:super_admin', Rule::unique('roles', 'name')->where('guard_name', 'web')],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::exists(Permission::class, 'name')->where('guard_name', 'web')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.not_in' => 'The protected super administrator role cannot be recreated.',
            'name.regex' => 'Role names may only contain letters, numbers, and underscores.',
        ];
    }
}
