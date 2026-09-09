<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Budi Utama, S.Pd.', 'email' => 'walikelas@kelas.test', 'role' => 'wali_kelas'],
            ['name' => 'Rangga Prasetyo', 'email' => 'ketua@kelas.test', 'role' => 'ketua_kelas'],
            ['name' => 'Anindya Larasati', 'email' => 'sekretaris@kelas.test', 'role' => 'sekretaris'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user + ['password' => 'password', 'is_active' => true],
            );
        }
    }
}
