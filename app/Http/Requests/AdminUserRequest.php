<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isWaliKelas();
    }

    public function rules(): array
    {
        $id = $this->route('admin')?->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'is_active' => ['boolean'],
            'password' => [$id ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
