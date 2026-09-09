<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /** Jam ke-N → [mulai, selesai]. Istirahat setelah JP4 sudah diperhitungkan. */
    private const JAM = [
        1 => ['07:00', '07:45'], 2 => ['07:45', '08:30'], 3 => ['08:30', '09:15'], 4 => ['09:15', '10:00'],
        5 => ['10:15', '11:00'], 6 => ['11:00', '11:45'], 7 => ['12:30', '13:15'], 8 => ['13:15', '14:00'],
        9 => ['14:00', '14:45'], 10 => ['14:45', '15:30'],
    ];

    /** Mapel per hari, tiap entri = [singkatan, jumlah JP berurutan]. */
    private const JADWAL = [
        'senin' => [['BIND', 2], ['MTK', 2], ['FIS', 2], ['PAI', 2], ['BING', 2]],
        'selasa' => [['MTK', 2], ['KIM', 2], ['BIO', 2], ['BIND', 2]],
        'rabu' => [['FIS', 2], ['BING', 2], ['MTK', 2], ['KIM', 2], ['PJOK', 2]],
        'kamis' => [['BIO', 2], ['BIND', 2], ['PAI', 2], ['FIS', 2]],
        'jumat' => [['PJOK', 2], ['MTK', 2], ['BING', 2], ['KIM', 2]],
    ];

    private const GURU = [
        'BIND' => 'Dra. Siti Aminah', 'MTK' => 'Drs. Hartono, M.Pd.', 'FIS' => 'Ir. Bambang Sutrisno',
        'KIM' => 'Yuni Kartika, S.Si.', 'BIO' => 'Endah Puspitasari, S.Pd.', 'BING' => 'Michael Tanuwijaya, S.S.',
        'PJOK' => 'Agus Salim, S.Or.', 'PAI' => 'H. Abdul Karim, S.Ag.',
    ];

    public function run(): void
    {
        $subjects = Subject::pluck('id', 'singkatan');

        foreach (self::JADWAL as $hari => $blok) {
            $jamKe = 1;

            foreach ($blok as [$singkatan, $durasi]) {
                for ($i = 0; $i < $durasi; $i++, $jamKe++) {
                    Schedule::updateOrCreate(
                        ['hari' => $hari, 'jam_ke' => $jamKe],
                        [
                            'subject_id' => $subjects[$singkatan],
                            'guru_pengampu' => self::GURU[$singkatan],
                            'jam_mulai' => self::JAM[$jamKe][0],
                            'jam_selesai' => self::JAM[$jamKe][1],
                        ],
                    );
                }
            }
        }
    }
}
