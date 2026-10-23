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

    /**
     * Peringatan siswa dengan kehadiran di bawah ambang (default 75%) selama periode
     * (misal 1 semester real-time), mencakup Absensi Harian Umum dan Absensi per Mata Pelajaran.
     *
     * @return array{
     *   ambang: float,
     *   mulai: CarbonImmutable,
     *   selesai: CarbonImmutable,
     *   hari_efektif: int,
     *   umum: Collection<int,object>,
     *   mapel: Collection<int,object>,
     *   mapel_items: Collection<int,object>,
     *   per_mapel_grup: Collection<int,object>,
     *   gabungan: Collection<int,object>,
     *   statistik: array<string,int>
     * }
     */
    public function peringatanSemester(CarbonImmutable $mulai, CarbonImmutable $selesai, float $ambang = 75.0): array
    {
        $semuaSiswa = $this->perSiswa($mulai, $selesai, true);

        $liburSubquery = DB::table('holidays')
            ->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->select('tanggal');

        $barisMapel = DB::table('subject_attendances as sa')
            ->join('schedules as sc', 'sc.id', '=', 'sa.schedule_id')
            ->join('subjects as s', 's.id', '=', 'sc.subject_id')
            ->join('students as st', 'st.id', '=', 'sa.student_id')
            ->where('st.is_active', true)
            ->where('s.is_active', true)
            ->whereBetween('sa.tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->whereNotIn('sa.tanggal', $liburSubquery)
            ->selectRaw("
                sa.student_id,
                s.id as subject_id,
                s.nama as nama_mapel,
                s.singkatan,
                s.warna,
                SUM(sa.status = 'hadir') as hadir,
                SUM(sa.status = 'sakit') as sakit,
                SUM(sa.status = 'izin') as izin,
                SUM(sa.status = 'alpa') as alpa,
                SUM(sa.status = 'dispensasi') as dispensasi,
                COUNT(*) as total
            ")
            ->groupBy('sa.student_id', 's.id', 's.nama', 's.singkatan', 's.warna')
            ->orderBy('s.nama')
            ->get()
            ->groupBy('student_id');

        $gabungan = collect();
        $umumRendah = collect();
        $mapelRendahPerSiswa = collect();
        $mapelItems = collect();

        foreach ($semuaSiswa as $b) {
            /** @var Student $siswa */
            $siswa = $b->siswa;
            $hadirEfektifUmum = $b->hitung['hadir'] + $b->hitung['dispensasi'];
            $tidakHadirUmum = $b->hitung['sakit'] + $b->hitung['izin'] + $b->hitung['alpa'];
            $isUmumRendah = $b->total > 0 && $b->persen < $ambang;

            $dataUmum = (object) [
                'hitung' => $b->hitung,
                'hadir_efektif' => $hadirEfektifUmum,
                'tidak_hadir' => $tidakHadirUmum,
                'total' => $b->total,
                'persen' => $b->persen,
                'selisih_persen' => $isUmumRendah ? round($ambang - $b->persen, 1) : 0.0,
                'butuh_hadir' => $isUmumRendah ? self::butuhHadirUntukAmbang($hadirEfektifUmum, $b->total, $ambang) : 0,
                'dibawah_ambang' => $isUmumRendah,
                'kategori' => $b->total > 0 ? self::kategoriRisiko($b->persen, $ambang) : 'belum_ada_data',
            ];

            $detailMapelSiswa = $barisMapel->get($siswa->id, collect())->map(function ($r) use ($ambang) {
                $hadir = (int) $r->hadir;
                $sakit = (int) $r->sakit;
                $izin = (int) $r->izin;
                $alpa = (int) $r->alpa;
                $dispensasi = (int) $r->dispensasi;
                $total = (int) $r->total;
                $hadirEfektif = $hadir + $dispensasi;
                $persen = self::persen($hadirEfektif, $total);
                $dibawah = $total > 0 && $persen < $ambang;

                return (object) [
                    'subject_id' => (int) $r->subject_id,
                    'nama' => (string) $r->nama_mapel,
                    'singkatan' => (string) $r->singkatan,
                    'warna' => (string) $r->warna,
                    'hadir' => $hadir,
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'alpa' => $alpa,
                    'dispensasi' => $dispensasi,
                    'hadir_efektif' => $hadirEfektif,
                    'total' => $total,
                    'persen' => $persen,
                    'selisih_persen' => $dibawah ? round($ambang - $persen, 1) : 0.0,
                    'butuh_jp' => $dibawah ? self::butuhHadirUntukAmbang($hadirEfektif, $total, $ambang) : 0,
                    'dibawah_ambang' => $dibawah,
                    'kategori' => $total > 0 ? self::kategoriRisiko($persen, $ambang) : 'belum_ada_data',
                ];
            });

            $totalJp = (int) $detailMapelSiswa->sum('total');
            $hadirEfektifJp = (int) $detailMapelSiswa->sum('hadir_efektif');
            $alpaJp = (int) $detailMapelSiswa->sum('alpa');
            $sakitJp = (int) $detailMapelSiswa->sum('sakit');
            $izinJp = (int) $detailMapelSiswa->sum('izin');
            $persenTotalJp = self::persen($hadirEfektifJp, $totalJp);
            $isTotalJpRendah = $totalJp > 0 && $persenTotalJp < $ambang;

            $ringkasanMapel = (object) [
                'hadir_efektif' => $hadirEfektifJp,
                'sakit' => $sakitJp,
                'izin' => $izinJp,
                'alpa' => $alpaJp,
                'total' => $totalJp,
                'persen' => $persenTotalJp,
                'butuh_jp' => $isTotalJpRendah ? self::butuhHadirUntukAmbang($hadirEfektifJp, $totalJp, $ambang) : 0,
                'dibawah_ambang' => $isTotalJpRendah,
                'jumlah_mapel_tercatat' => $detailMapelSiswa->count(),
            ];

            $mapelBermasalah = $detailMapelSiswa
                ->where('dibawah_ambang', true)
                ->sortBy([
                    ['persen', 'asc'],
                    ['alpa', 'desc'],
                    ['nama', 'asc'],
                ])
                ->values();

            $isMapelRendah = $mapelBermasalah->isNotEmpty() || $isTotalJpRendah;

            if (! $isUmumRendah && ! $isMapelRendah) {
                continue;
            }

            $kandidatPersen = [];
            if ($isUmumRendah) {
                $kandidatPersen[] = $dataUmum->persen;
            }
            if ($mapelBermasalah->isNotEmpty()) {
                $kandidatPersen[] = (float) $mapelBermasalah->min('persen');
            }
            if ($isTotalJpRendah) {
                $kandidatPersen[] = $persenTotalJp;
            }

            $persenTerendah = $kandidatPersen !== [] ? min($kandidatPersen) : 0.0;
            $kategoriGabungan = self::kategoriRisiko($persenTerendah, $ambang);

            $itemSiswa = (object) [
                'siswa' => $siswa,
                'umum' => $dataUmum,
                'ringkasan_mapel' => $ringkasanMapel,
                'mapel_rendah' => $mapelBermasalah,
                'semua_mapel' => $detailMapelSiswa,
                'is_umum_rendah' => $isUmumRendah,
                'is_mapel_rendah' => $isMapelRendah,
                'persen_terendah' => $persenTerendah,
                'kategori' => $kategoriGabungan,
                'wa_url' => self::buatTautanWaOrtu($siswa, $dataUmum, $mapelBermasalah, $ringkasanMapel, $mulai, $selesai, $ambang),
            ];

            $gabungan->push($itemSiswa);

            if ($isUmumRendah) {
                $umumRendah->push($itemSiswa);
            }

            if ($isMapelRendah) {
                $mapelRendahPerSiswa->push($itemSiswa);

                foreach ($mapelBermasalah as $m) {
                    $mapelItems->push((object) [
                        'siswa' => $siswa,
                        'mapel' => $m,
                        'wa_url' => $itemSiswa->wa_url,
                    ]);
                }
            }
        }

        $umumRendah = $umumRendah->sortBy([
            fn ($a, $b) => $a->umum->persen <=> $b->umum->persen,
            fn ($a, $b) => $b->umum->hitung['alpa'] <=> $a->umum->hitung['alpa'],
            fn ($a, $b) => $a->siswa->no_absen <=> $b->siswa->no_absen,
        ])->values();

        $mapelRendahPerSiswa = $mapelRendahPerSiswa->sortBy([
            fn ($a, $b) => ($a->mapel_rendah->min('persen') ?? $a->ringkasan_mapel->persen) <=> ($b->mapel_rendah->min('persen') ?? $b->ringkasan_mapel->persen),
            fn ($a, $b) => $b->mapel_rendah->count() <=> $a->mapel_rendah->count(),
            fn ($a, $b) => $a->siswa->no_absen <=> $b->siswa->no_absen,
        ])->values();

        $mapelItems = $mapelItems->sortBy([
            fn ($a, $b) => $a->mapel->persen <=> $b->mapel->persen,
            fn ($a, $b) => $b->mapel->alpa <=> $a->mapel->alpa,
            fn ($a, $b) => $a->siswa->no_absen <=> $b->siswa->no_absen,
        ])->values();

        $perMapelGrup = $mapelItems
            ->groupBy(fn ($item) => $item->mapel->subject_id)
            ->map(function (Collection $grup) {
                $firstMapel = $grup->first()->mapel;

                return (object) [
                    'subject_id' => $firstMapel->subject_id,
                    'nama' => $firstMapel->nama,
                    'singkatan' => $firstMapel->singkatan,
                    'warna' => $firstMapel->warna,
                    'jumlah_siswa' => $grup->count(),
                    'terendah' => (float) $grup->min(fn ($x) => $x->mapel->persen),
                ];
            })
            ->sortByDesc('jumlah_siswa')
            ->values();

        $gabungan = $gabungan->sortBy([
            fn ($a, $b) => $a->persen_terendah <=> $b->persen_terendah,
            fn ($a, $b) => ($b->is_umum_rendah && $b->is_mapel_rendah) <=> ($a->is_umum_rendah && $a->is_mapel_rendah),
            fn ($a, $b) => $a->siswa->no_absen <=> $b->siswa->no_absen,
        ])->values();

        return [
            'ambang' => $ambang,
            'mulai' => $mulai,
            'selesai' => $selesai,
            'hari_efektif' => count($this->hariEfektif($mulai, $selesai)),
            'umum' => $umumRendah,
            'mapel' => $mapelRendahPerSiswa,
            'mapel_items' => $mapelItems,
            'per_mapel_grup' => $perMapelGrup,
            'gabungan' => $gabungan,
            'statistik' => [
                'total_siswa_berisiko' => $gabungan->count(),
                'jumlah_umum_rendah' => $umumRendah->count(),
                'jumlah_mapel_rendah' => $mapelRendahPerSiswa->count(),
                'jumlah_kasus_mapel' => $mapelItems->count(),
                'jumlah_keduanya' => $gabungan->filter(fn ($x) => $x->is_umum_rendah && $x->is_mapel_rendah)->count(),
                'kritis' => $gabungan->where('kategori', 'kritis')->count(),
                'rawan' => $gabungan->where('kategori', 'rawan')->count(),
                'waspada' => $gabungan->where('kategori', 'waspada')->count(),
            ],
        ];
    }

    /**
     * Menghitung berapa kali hadir berturut-turut yang dibutuhkan agar persentase kembali >= $ambang.
     */
    public static function butuhHadirUntukAmbang(int $hadirEfektif, int $total, float $ambang = 75.0): int
    {
        if ($total <= 0 || $ambang <= 0 || $ambang >= 100) {
            return 0;
        }

        if (self::persen($hadirEfektif, $total) >= $ambang) {
            return 0;
        }

        $x = (int) max(1, (int) floor((($ambang * $total) - (100 * $hadirEfektif)) / (100 - $ambang)));
        while (self::persen($hadirEfektif + $x, $total + $x) < $ambang) {
            $x++;
        }

        return $x;
    }

    /**
     * Kategori tingkat keparahan risiko kehadiran di bawah ambang.
     */
    public static function kategoriRisiko(float $persen, float $ambang = 75.0): string
    {
        if ($persen < 50.0) {
            return 'kritis';
        }
        if ($persen < 65.0) {
            return 'rawan';
        }
        if ($persen < $ambang) {
            return 'waspada';
        }

        return 'aman';
    }

    /**
     * Membuat tautan WhatsApp otomatis ke orang tua/wali siswa bila nomor HP tersedia.
     */
    public static function buatTautanWaOrtu(
        Student $siswa,
        object $umum,
        Collection $mapelRendah,
        object $ringkasanMapel,
        CarbonImmutable $mulai,
        CarbonImmutable $selesai,
        float $ambang = 75.0
    ): ?string {
        if (blank($siswa->no_hp_ortu)) {
            return null;
        }

        $hp = preg_replace('/\D+/', '', (string) $siswa->no_hp_ortu);
        if ($hp === '') {
            return null;
        }
        if (str_starts_with($hp, '0')) {
            $hp = '62'.substr($hp, 1);
        } elseif (str_starts_with($hp, '8')) {
            $hp = '62'.$hp;
        }

        $namaKelas = (string) Setting::get('nama_kelas', 'Kelas');
        $namaSemester = $mulai->month >= 7 ? 'Semester Ganjil' : 'Semester Genap';
        $rentang = \App\Support\Tanggal::pendek($mulai).' s.d. '.\App\Support\Tanggal::pendek($selesai);

        $barisPesan = [
            "Yth. Bapak/Ibu Orang Tua/Wali dari *{$siswa->nama}* (No. Absen {$siswa->no_absen}),",
            '',
            "Melalui pesan ini kami informasikan bahwa persentase kehadiran siswa di kelas *{$namaKelas}* selama *{$namaSemester}* ({$rentang}) saat ini berada di bawah batas minimal *{$ambang}%*:",
        ];

        if ($umum->dibawah_ambang) {
            $barisPesan[] = sprintf(
                "- *Absensi Harian Umum*: *%s%%* (Hadir: %d, Sakit: %d, Izin: %d, Alpa: %d, Dispensasi: %d dari %d hari tercatat). Perlu +%d hari hadir berturut-turut untuk mencapai %s%%.",
                $umum->persen,
                $umum->hitung['hadir'],
                $umum->hitung['sakit'],
                $umum->hitung['izin'],
                $umum->hitung['alpa'],
                $umum->hitung['dispensasi'],
                $umum->total,
                $umum->butuh_hadir,
                $ambang
            );
        }

        if ($mapelRendah->isNotEmpty()) {
            $daftarMapel = $mapelRendah
                ->map(fn ($m) => "{$m->nama} ({$m->persen}% - {$m->hadir_efektif}/{$m->total} JP)")
                ->implode(', ');
            $barisPesan[] = "- *Absensi Mata Pelajaran < {$ambang}%*: {$daftarMapel}.";
        } elseif ($ringkasanMapel->dibawah_ambang) {
            $barisPesan[] = "- *Total Kehadiran Jam Pelajaran (JP)*: *{$ringkasanMapel->persen}%* ({$ringkasanMapel->hadir_efektif}/{$ringkasanMapel->total} JP).";
        }

        $barisPesan[] = '';
        $barisPesan[] = 'Mohon kerja sama Bapak/Ibu untuk memantau dan meningkatkan kehadiran putra/putrinya. Terima kasih.';

        return 'https://wa.me/'.$hp.'?text='.rawurlencode(implode("\n", $barisPesan));
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
