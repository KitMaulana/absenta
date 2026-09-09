<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('no_absen');
            $table->string('nama');
            $table->string('nisn')->nullable()->unique();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('no_hp_ortu')->nullable();
            $table->string('foto')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'no_absen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
