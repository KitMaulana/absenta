<?php

namespace App\Http\Requests;

use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        $id = $this->route('jadwal')?->id;

        return [
            'hari' => ['required', Rule::in(array_keys(Schedule::HARI))],
            'jam_ke' => [
                'required', 'integer', 'min:1', 'max:12',
                Rule::unique('schedules', 'jam_ke')
                    ->where(fn ($q) => $q->where('hari', $this->input('hari')))
                    ->ignore($id),
            ],
            'subject_id' => ['required', 'exists:subjects,id'],
            'guru_pengampu' => ['nullable', 'string', 'max:100'],
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
        ];
    }

    public function messages(): array
    {
        return [
            'jam_ke.unique' => 'Jam ke-:input pada hari tersebut sudah terisi mata pelajaran lain.',
            'jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.',
        ];
    }
}
