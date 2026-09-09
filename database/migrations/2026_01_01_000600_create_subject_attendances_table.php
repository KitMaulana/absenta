<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('tanggal');
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpa', 'dispensasi'])->default('hadir');
            $table->string('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'tanggal', 'schedule_id'], 'subject_att_unique');
            $table->index(['tanggal', 'schedule_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_attendances');
    }
};
