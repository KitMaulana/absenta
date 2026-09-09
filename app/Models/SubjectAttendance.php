<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectAttendance extends Model
{
    protected $fillable = ['student_id', 'tanggal', 'schedule_id', 'status', 'keterangan', 'created_by'];

    protected function casts(): array
    {
        return ['status' => AttendanceStatus::class];
    }

    /** Lihat catatan di DailyAttendance: kolom DATE disimpan sebagai Y-m-d. */
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

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
