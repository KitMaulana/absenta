<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyAttendance extends Model
{
    protected $fillable = ['student_id', 'tanggal', 'status', 'keterangan', 'created_by'];

    protected function casts(): array
    {
        return ['status' => AttendanceStatus::class];
    }

    /**
     * Kolom DATE selalu disimpan sebagai Y-m-d. Cast bawaan 'date' menuliskan
     * 'Y-m-d H:i:s'; MySQL memotongnya diam-diam, tetapi mesin lain tidak,
     * sehingga updateOrCreate gagal menemukan baris yang sudah ada.
     */
    protected function tanggal(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => CarbonImmutable::parse($value),
            set: fn ($value) => CarbonImmutable::parse($value)->toDateString(),
        );
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
