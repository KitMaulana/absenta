<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'no_absen', 'nama', 'nisn', 'jenis_kelamin', 'no_hp_ortu', 'foto', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeUrut(Builder $q): Builder
    {
        return $q->orderBy('no_absen')->orderBy('nama');
    }

    public function dailyAttendances(): HasMany
    {
        return $this->hasMany(DailyAttendance::class);
    }

    public function subjectAttendances(): HasMany
    {
        return $this->hasMany(SubjectAttendance::class);
    }
}
