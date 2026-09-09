<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['tanggal', 'keterangan'];

    /** Lihat catatan di DailyAttendance: kolom DATE disimpan sebagai Y-m-d. */
    protected function tanggal(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => CarbonImmutable::parse($value),
            set: fn ($value) => CarbonImmutable::parse($value)->toDateString(),
        );
    }
}
