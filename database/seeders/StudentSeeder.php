<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /** 18 laki-laki + 18 perempuan, diurutkan alfabetis lalu diberi no absen. */
    public function run(): void
    {
        $laki = [
            'Ahmad Fauzan Ramadhan', 'Bagas Nur Cahyo', 'Bayu Aji Nugroho', 'Dimas Arya Pratama',
            'Fajar Ilham Maulana', 'Farhan Adi Wibowo', 'Galih Satria Wicaksono', 'Hendra Kurniawan',
            'Ikhsan Nur Rahman', 'Kevin Dwi Saputra', 'Muhammad Rizky Alfarizi', 'Naufal Hakim Prasetya',
            'Panji Bagaskara', 'Raka Adiputra', 'Rangga Wijaya Kusuma', 'Satria Bagus Pamungkas',
            'Wahyu Setiawan', 'Yoga Pratama Putra',
        ];

        $perempuan = [
            'Adelia Putri Maharani', 'Alya Rahmawati', 'Anindita Salsabila', 'Bunga Citra Lestari',
            'Cinta Aulia Ramadhani', 'Dewi Anggraini', 'Fitri Nur Hasanah', 'Gita Permata Sari',
            'Intan Nuraini', 'Kartika Ayu Wulandari', 'Laras Ayu Pitaloka', 'Maharani Dwi Utami',
            'Nabila Zahra Amelia', 'Olivia Putri Ananda', 'Putri Ayu Lestari', 'Rina Marlina Sari',
            'Syifa Kamila Azzahra', 'Tiara Nur Fadhilah',
        ];

        $semua = array_merge(
            array_map(fn ($n) => ['nama' => $n, 'jenis_kelamin' => 'L'], $laki),
            array_map(fn ($n) => ['nama' => $n, 'jenis_kelamin' => 'P'], $perempuan),
        );

        usort($semua, fn ($a, $b) => strcmp($a['nama'], $b['nama']));

        foreach ($semua as $i => $siswa) {
            Student::updateOrCreate(
                ['nama' => $siswa['nama']],
                [
                    'no_absen' => $i + 1,
                    'jenis_kelamin' => $siswa['jenis_kelamin'],
                    'nisn' => sprintf('00%08d', 91230000 + $i + 1),
                    'no_hp_ortu' => sprintf('08%d', random_int(1000000000, 9999999999)),
                    'is_active' => true,
                ],
            );
        }
    }
}
