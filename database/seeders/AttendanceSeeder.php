<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Models\Holiday;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /** Bobot status tidak-hadir agar sebaran mirip kelas nyata (total ~8%). */
    private const BOBOT = [
        'hadir' => 920, 'sakit' => 30, 'izin' => 25, 'alpa' => 18, 'dispensasi' => 7,
    ];

    public function run(): void
    {
        $students = Student::aktif()->pluck('id')->all();
        $schedules = Schedule::get(['id', 'hari'])->groupBy('hari');
        $creator = User::where('role', 'wali_kelas')->value('id');
        $hariAktif = Setting::hariAktif();
        $libur = Holiday::pluck('tanggal')->map(fn ($d) => $d->toDateString())->flip();

        if ($students === []) {
            return;
        }

        $harian = [];
        $mapel = [];
        $now = now();

        // Hari ini disertakan supaya dashboard & halaman publik langsung ada isinya.
        for ($offset = 29; $offset >= 0; $offset--) {
            $tanggal = CarbonImmutable::today()->subDays($offset);
            $hari = Schedule::hariDari($tanggal);

            if (! in_array($hari, $hariAktif, true) || $libur->has($tanggal->toDateString())) {
                continue;
            }

            foreach ($students as $studentId) {
                $status = $this->acakStatus();

                $harian[] = [
                    'student_id' => $studentId,
                    'tanggal' => $tanggal->toDateString(),
                    'status' => $status,
                    'keterangan' => $this->keterangan($status),
                    'created_by' => $creator,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                foreach ($schedules->get($hari, collect()) as $schedule) {
                    // Siswa yang absen seharian tetap absen di semua JP; yang hadir
                    // sesekali bolos satu-dua JP saja.
                    $statusJp = $status !== 'hadir'
                        ? $status
                        : (random_int(1, 100) <= 2 ? 'alpa' : 'hadir');

                    $mapel[] = [
                        'student_id' => $studentId,
                        'tanggal' => $tanggal->toDateString(),
                        'schedule_id' => $schedule->id,
                        'status' => $statusJp,
                        'keterangan' => null,
                        'created_by' => $creator,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        DB::transaction(function () use ($harian, $mapel) {
            DB::table('subject_attendances')->delete();
            DB::table('daily_attendances')->delete();

            foreach (array_chunk($harian, 500) as $chunk) {
                DB::table('daily_attendances')->insert($chunk);
            }
            foreach (array_chunk($mapel, 500) as $chunk) {
                DB::table('subject_attendances')->insert($chunk);
            }
        });

        $this->command?->info(sprintf('Absensi dummy: %d harian, %d per mapel.', count($harian), count($mapel)));
    }

    private function acakStatus(): string
    {
        $undian = random_int(1, array_sum(self::BOBOT));

        foreach (self::BOBOT as $status => $bobot) {
            if (($undian -= $bobot) <= 0) {
                return $status;
            }
        }

        return AttendanceStatus::Hadir->value;
    }

    private function keterangan(string $status): ?string
    {
        return match ($status) {
            'sakit' => 'Surat keterangan sakit',
            'izin' => 'Izin keperluan keluarga',
            'dispensasi' => 'Mengikuti lomba mewakili sekolah',
            default => null,
        };
    }
}
