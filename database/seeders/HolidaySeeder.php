<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /** Beberapa libur contoh dalam rentang data dummy (30 hari terakhir). */
    public function run(): void
    {
        $hariIni = CarbonImmutable::today();

        $libur = [
            [$hariIni->subDays(21), 'Libur Peringatan Hari Besar'],
            [$hariIni->subDays(9), 'Kegiatan Tengah Semester'],
        ];

        foreach ($libur as [$tanggal, $keterangan]) {
            if ($tanggal->isWeekend()) {
                continue;
            }
            Holiday::updateOrCreate(
                ['tanggal' => $tanggal->toDateString()],
                ['keterangan' => $keterangan],
            );
        }
    }
}
