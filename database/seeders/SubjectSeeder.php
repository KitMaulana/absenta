<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['nama' => 'Bahasa Indonesia', 'singkatan' => 'BIND', 'warna' => '#ef4444'],
            ['nama' => 'Matematika', 'singkatan' => 'MTK', 'warna' => '#3b82f6'],
            ['nama' => 'Fisika', 'singkatan' => 'FIS', 'warna' => '#8b5cf6'],
            ['nama' => 'Kimia', 'singkatan' => 'KIM', 'warna' => '#14b8a6'],
            ['nama' => 'Biologi', 'singkatan' => 'BIO', 'warna' => '#22c55e'],
            ['nama' => 'Bahasa Inggris', 'singkatan' => 'BING', 'warna' => '#f97316'],
            ['nama' => 'PJOK', 'singkatan' => 'PJOK', 'warna' => '#06b6d4'],
            ['nama' => 'PAI', 'singkatan' => 'PAI', 'warna' => '#a16207'],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(['singkatan' => $subject['singkatan']], $subject + ['is_active' => true]);
        }
    }
}
