<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        $id = $this->route('libur')?->id;

        return [
            'tanggal' => ['required', 'date', Rule::unique('holidays', 'tanggal')->ignore($id)],
            'keterangan' => ['required', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return ['tanggal.unique' => 'Tanggal tersebut sudah terdaftar sebagai hari libur.'];
    }
}
