<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        $id = $this->route('siswa')?->id;

        return [
            'no_absen' => ['required', 'integer', 'min:1', 'max:999'],
            'nama' => ['required', 'string', 'max:100'],
            'nisn' => ['nullable', 'string', 'max:20', Rule::unique('students', 'nisn')->ignore($id)],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'no_hp_ortu' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'nisn' => $this->filled('nisn') ? trim($this->input('nisn')) : null,
        ]);
    }
}
