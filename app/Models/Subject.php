<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['nama', 'singkatan', 'warna', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
