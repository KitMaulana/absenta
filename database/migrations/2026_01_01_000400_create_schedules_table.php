<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu']);
            $table->unsignedTinyInteger('jam_ke');
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('guru_pengampu')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->timestamps();

            $table->unique(['hari', 'jam_ke']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
