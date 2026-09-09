<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'singkatan' => ['required', 'string', 'max:20'],
            'warna' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function messages(): array
    {
        return ['warna.regex' => 'Warna harus berupa kode heksadesimal, contoh #3b82f6.'];
    }
}
