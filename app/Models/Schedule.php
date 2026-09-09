<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    public const HARI = [
        'senin' => 'Senin',
        'selasa' => 'Selasa',
        'rabu' => 'Rabu',
        'kamis' => 'Kamis',
        'jumat' => 'Jumat',
        'sabtu' => 'Sabtu',
        'minggu' => 'Minggu',
    ];

    protected $fillable = ['hari', 'jam_ke', 'subject_id', 'guru_pengampu', 'jam_mulai', 'jam_selesai'];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function subjectAttendances(): HasMany
    {
        return $this->hasMany(SubjectAttendance::class);
    }

    public function hariLabel(): string
    {
        return self::HARI[$this->hari] ?? $this->hari;
    }

    public function jamRentang(): ?string
    {
        if (! $this->jam_mulai || ! $this->jam_selesai) {
            return null;
        }

        return substr((string) $this->jam_mulai, 0, 5).'–'.substr((string) $this->jam_selesai, 0, 5);
    }

    /** Nama hari (lowercase, tanpa akses locale) dari sebuah tanggal. */
    public static function hariDari(\DateTimeInterface $date): string
    {
        return array_keys(self::HARI)[((int) $date->format('N')) - 1];
    }
}
