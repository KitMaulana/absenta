<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Holiday;
use App\Models\Setting;
use App\Models\Student;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Semua perhitungan agregat absensi berkumpul di sini supaya angka di
 * dashboard, halaman publik, dan PDF selalu memakai rumus yang sama.
 */
class RekapService
{
    /** Kerangka hitungan kosong: semua status bernilai 0. */
    public static function hitunganKosong(): array
    {
        return array_fill_keys(AttendanceStatus::values(), 0);
    }

    /**
     * Tanggal efektif sekolah dalam rentang: hanya hari aktif dan bukan hari libur.
     *
     * @return array<int,string> daftar tanggal Y-m-d
     */
    public function hariEfektif(CarbonImmutable $mulai, CarbonImmutable $selesai): array
    {
        $hariAktif = Setting::hariAktif();
        $libur = Holiday::whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->pluck('tanggal')
            ->map(fn ($d) => $d->toDateString())
            ->flip();

        $out = [];

        foreach (CarbonPeriod::create($mulai, $selesai) as $tanggal) {
            $hari = array_keys(\App\Models\Schedule::HARI)[((int) $tanggal->format('N')) - 1];

            if (in_array($hari, $hariAktif, true) && ! $libur->has($tanggal->format('Y-m-d'))) {
                $out[] = $tanggal->format('Y-m-d');
            }
        }

        return $out;
    }

    /** Rekap absensi umum satu hari: jumlah per status. */
    public function ringkasanHarian(CarbonImmutable $tanggal): array
    {
        $libur = Holiday::whereDate('tanggal', $tanggal)->first();
        if ($libur) {
            $hasil = self::hitunganKosong();
            $hasil['total'] = 0;
            $hasil['siswa_aktif'] = Student::aktif()->count();
            $hasil['sudah_diinput'] = false;
            $hasil['persen'] = 0.0;
            $hasil['is_libur'] = true;
            $hasil['libur'] = $libur;

            return $hasil;
        }

        $hitung = DB::table('daily_attendances')
            ->where('tanggal', $tanggal->toDateString())
            ->join('students', 'students.id', '=', 'daily_attendances.student_id')
            ->where('students.is_active', true)
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status')
            ->all();

        $hasil = array_merge(self::hitunganKosong(), $hitung);
        $hasil['total'] = array_sum($hasil);
        $hasil['siswa_aktif'] = Student::aktif()->count();
        $hasil['sudah_diinput'] = $hasil['total'] > 0;
        $hasil['persen'] = self::persen($hasil['hadir'] + $hasil['dispensasi'], $hasil['total']);
        $hasil['is_libur'] = false;
        $hasil['libur'] = null;

        return $hasil;
    }

    /** Rekap absensi umum untuk rentang tanggal: jumlah per status. */
    public function ringkasanPeriode(CarbonImmutable $mulai, CarbonImmutable $selesai): array
    {
        $hitung = DB::table('daily_attendances')
            ->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->whereNotIn('tanggal', DB::table('holidays')->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])->select('tanggal'))
            ->join('students', 'students.id', '=', 'daily_attendances.student_id')
            ->where('students.is_active', true)
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status')
            ->all();

        $hasil = array_merge(self::hitunganKosong(), $hitung);
        $hasil['total'] = array_sum($hasil);
        $hasil['persen'] = self::persen($hasil['hadir'] + $hasil['dispensasi'], $hasil['total']);

        return $hasil;
    }

    /**
     * Tren persentase kehadiran harian.
     *
     * @return array{labels: array<int,string>, persen: array<int,float>, tanggal: array<int,string>}
     */
    public function trenHarian(CarbonImmutable $mulai, CarbonImmutable $selesai): array
    {
        $baris = DB::table('daily_attendances')
            ->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->join('students', 'students.id', '=', 'daily_attendances.student_id')
            ->where('students.is_active', true)
            ->selectRaw("tanggal, SUM(status IN ('hadir','dispensasi')) as hadir, COUNT(*) as total")
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get()
            ->keyBy(fn ($r) => (string) $r->tanggal);

        $labels = $persen = $tanggal = [];

        foreach ($this->hariEfektif($mulai, $selesai) as $hari) {
            $row = $baris->get($hari);

            if (! $row) {
                continue; // hari sekolah yang belum diinput tidak digambar
            }

            $d = CarbonImmutable::parse($hari);
            $tanggal[] = $hari;
            $labels[] = $d->format('j').' '.substr(\App\Support\Tanggal::BULAN[$d->month], 0, 3);
            $persen[] = self::persen((int) $row->hadir, (int) $row->total);
        }

        return compact('labels', 'persen', 'tanggal');
    }

    /**
     * Persentase kehadiran per mata pelajaran dalam suatu periode.
     *
     * @return Collection<int,object{nama:string,singkatan:string,warna:string,hadir:int,total:int,persen:float}>
     */
    public function persenPerMapel(CarbonImmutable $mulai, CarbonImmutable $selesai): Collection
    {
        return DB::table('subject_attendances as sa')
            ->join('schedules as sc', 'sc.id', '=', 'sa.schedule_id')
            ->join('subjects as s', 's.id', '=', 'sc.subject_id')
            ->join('students as st', 'st.id', '=', 'sa.student_id')
            ->where('st.is_active', true)
            ->whereBetween('sa.tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->whereNotIn('sa.tanggal', DB::table('holidays')->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])->select('tanggal'))
            ->selectRaw("s.id, s.nama, s.singkatan, s.warna,
                         SUM(sa.status IN ('hadir','dispensasi')) as hadir,
                         COUNT(*) as total")
            ->groupBy('s.id', 's.nama', 's.singkatan', 's.warna')
            ->orderBy('s.nama')
            ->get()
            ->map(function ($r) {
                $r->hadir = (int) $r->hadir;
                $r->total = (int) $r->total;
                $r->persen = self::persen($r->hadir, $r->total);

                return $r;
            });
    }

    /**
     * Rekap absensi umum per siswa dalam periode: jumlah tiap status + persentase.
     *
     * @return Collection<int,object>
     */
    public function perSiswa(CarbonImmutable $mulai, CarbonImmutable $selesai, bool $hanyaAktif = true): Collection
    {
        $hitung = DB::table('daily_attendances')
            ->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->whereNotIn('tanggal', DB::table('holidays')->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])->select('tanggal'))
            ->selectRaw('student_id, status, COUNT(*) as jumlah')
            ->groupBy('student_id', 'status')
            ->get()
            ->groupBy('student_id');

        return Student::query()
            ->when($hanyaAktif, fn ($q) => $q->aktif())
            ->urut()
            ->get()
            ->map(function (Student $siswa) use ($hitung) {
                $baris = self::hitunganKosong();

                foreach ($hitung->get($siswa->id, collect()) as $r) {
                    $baris[$r->status] = (int) $r->jumlah;
                }

                $total = array_sum($baris);

                return (object) [
                    'siswa' => $siswa,
                    'hitung' => $baris,
                    'total' => $total,
                    'persen' => self::persen($baris['hadir'] + $baris['dispensasi'], $total),
                ];
            });
    }

    /**
     * Matriks siswa × tanggal berisi kode status (H/S/I/A/D) untuk rekap tabel.
     *
     * @return array<int,array<string,string>> [student_id => [Y-m-d => kode]]
     */
    public function matriksHarian(CarbonImmutable $mulai, CarbonImmutable $selesai): array
    {
        $matriks = [];

        DB::table('daily_attendances')
            ->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->whereNotIn('tanggal', DB::table('holidays')->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])->select('tanggal'))
            ->select('student_id', 'tanggal', 'status')
            ->orderBy('tanggal')
            ->chunk(2000, function ($rows) use (&$matriks) {
                foreach ($rows as $r) {
                    $matriks[$r->student_id][(string) $r->tanggal] = AttendanceStatus::from($r->status)->kode();
                }
            });

        return $matriks;
    }

    /**
     * Rekap kehadiran satu mapel per siswa.
     *
     * @return Collection<int,object>
     */
    public function perSiswaUntukMapel(int $subjectId, CarbonImmutable $mulai, CarbonImmutable $selesai): Collection
    {
        $hitung = DB::table('subject_attendances as sa')
            ->join('schedules as sc', 'sc.id', '=', 'sa.schedule_id')
            ->where('sc.subject_id', $subjectId)
            ->whereBetween('sa.tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->whereNotIn('sa.tanggal', DB::table('holidays')->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])->select('tanggal'))
            ->selectRaw('sa.student_id, sa.status, COUNT(*) as jumlah')
            ->groupBy('sa.student_id', 'sa.status')
            ->get()
            ->groupBy('student_id');

        return Student::aktif()->urut()->get()->map(function (Student $siswa) use ($hitung) {
            $baris = self::hitunganKosong();

            foreach ($hitung->get($siswa->id, collect()) as $r) {
                $baris[$r->status] = (int) $r->jumlah;
            }

            $total = array_sum($baris);

            return (object) [
                'siswa' => $siswa,
                'hitung' => $baris,
                'total' => $total,
                'persen' => self::persen($baris['hadir'] + $baris['dispensasi'], $total),
            ];
        });
    }

    /** Rekap satu siswa di seluruh mapel — untuk laporan ke orang tua. */
    public function mapelUntukSiswa(int $studentId, CarbonImmutable $mulai, CarbonImmutable $selesai): Collection
    {
        return DB::table('subject_attendances as sa')
            ->join('schedules as sc', 'sc.id', '=', 'sa.schedule_id')
            ->join('subjects as s', 's.id', '=', 'sc.subject_id')
            ->where('sa.student_id', $studentId)
            ->whereBetween('sa.tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->whereNotIn('sa.tanggal', DB::table('holidays')->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])->select('tanggal'))
            ->selectRaw("s.nama, s.singkatan, s.warna,
                         SUM(sa.status = 'hadir') as hadir,
                         SUM(sa.status = 'sakit') as sakit,
                         SUM(sa.status = 'izin') as izin,
                         SUM(sa.status = 'alpa') as alpa,
                         SUM(sa.status = 'dispensasi') as dispensasi,
                         COUNT(*) as total")
            ->groupBy('s.id', 's.nama', 's.singkatan', 's.warna')
            ->orderBy('s.nama')
            ->get()
            ->map(function ($r) {
                foreach (['hadir', 'sakit', 'izin', 'alpa', 'dispensasi', 'total'] as $k) {
                    $r->$k = (int) $r->$k;
                }
                $r->persen = self::persen($r->hadir + $r->dispensasi, $r->total);

                return $r;
            });
    }

    /**
     * Siswa dengan alpa >= $ambang dalam periode — dipakai untuk daftar merah dashboard.
     *
     * @return Collection<int,object>
     */
    public function siswaRawanAlpa(CarbonImmutable $mulai, CarbonImmutable $selesai, int $ambang = 3): Collection
    {
        return DB::table('daily_attendances as da')
            ->join('students as s', 's.id', '=', 'da.student_id')
            ->where('s.is_active', true)
            ->where('da.status', 'alpa')
            ->whereBetween('da.tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->whereNotIn('da.tanggal', DB::table('holidays')->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])->select('tanggal'))
            ->selectRaw('s.id, s.nama, s.no_absen, COUNT(*) as alpa')
            ->groupBy('s.id', 's.nama', 's.no_absen')
            ->havingRaw('COUNT(*) >= ?', [$ambang])
            ->orderByDesc('alpa')
            ->get();
    }

    /** Daftar siswa yang tidak hadir pada satu tanggal (untuk halaman publik). */
    public function tidakHadirPada(CarbonImmutable $tanggal): Collection
    {
        if (Holiday::whereDate('tanggal', $tanggal)->exists()) {
            return collect();
        }

        return DB::table('daily_attendances as da')
            ->join('students as s', 's.id', '=', 'da.student_id')
            ->where('s.is_active', true)
            ->where('da.tanggal', $tanggal->toDateString())
            ->where('da.status', '!=', 'hadir')
            ->select('s.nama', 's.no_absen', 'da.status', 'da.keterangan')
            ->orderBy('s.no_absen')
            ->get();
    }

    public static function persen(int $bagian, int $total): float
    {
        return $total > 0 ? round($bagian / $total * 100, 1) : 0.0;
    }
}
