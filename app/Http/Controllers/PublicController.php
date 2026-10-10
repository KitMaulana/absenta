<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\Student;
use App\Services\RekapService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman tanpa login untuk orang tua dan siswa.
 *
 * Aturan privasi: hanya nama, nomor absen, dan angka agregat yang boleh keluar
 * dari controller ini. NISN dan nomor HP orang tua tidak pernah dikirim.
 */
class PublicController extends Controller
{
    public function __construct(private readonly RekapService $rekap) {}

    public function index(Request $request): View
    {
        $hariIni = CarbonImmutable::today();
        $liburHariIni = Holiday::whereDate('tanggal', $hariIni)->first();

        // Resolusi bulan & tahun yang dipilih
        $daftarBulan = \App\Support\Periode::daftarBulan();
        $selectedBulan = (int) $request->input('bulan', 0);
        $selectedTahun = (int) $request->input('tahun', 0);

        if ($request->filled('bulan_tahun')) {
            try {
                $bt = CarbonImmutable::parse($request->input('bulan_tahun').'-01');
                $selectedBulan = $bt->month;
                $selectedTahun = $bt->year;
            } catch (\Throwable) {}
        }

        if ($selectedBulan < 1 || $selectedBulan > 12 || $selectedTahun < 2020 || $selectedTahun > 2035) {
            // Periksa apakah bulan berjalan memiliki data di DB
            $adaBulanIni = \Illuminate\Support\Facades\DB::table('daily_attendances')
                ->whereBetween('tanggal', [$hariIni->startOfMonth()->toDateString(), $hariIni->endOfMonth()->toDateString()])
                ->exists();

            if (! $adaBulanIni) {
                $maxTanggal = \Illuminate\Support\Facades\DB::table('daily_attendances')->max('tanggal');
                if ($maxTanggal) {
                    $maxD = CarbonImmutable::parse($maxTanggal);
                    $selectedBulan = $maxD->month;
                    $selectedTahun = $maxD->year;
                } else {
                    $selectedBulan = $hariIni->month;
                    $selectedTahun = $hariIni->year;
                }
            } else {
                $selectedBulan = $hariIni->month;
                $selectedTahun = $hariIni->year;
            }
        }

        $bulanTerpilihDate = CarbonImmutable::createFromDate($selectedTahun, $selectedBulan, 1)->startOfDay();

        // Periode untuk grafik (default mengikuti bulan terpilih)
        [$mulaiGrafik, $selesaiGrafik, $labelGrafik, $pilihanGrafik] = $this->periode($request, $hariIni, $bulanTerpilihDate);

        // Filter rentang untuk rekap tabel siswa: hari_ini, mingguan, bulanan, semester
        $filterRekap = $request->input('filter_rekap', 'hari_ini');
        $namaSemester = $hariIni->month >= 7 ? 'Semester Ganjil' : 'Semester Genap';
        $semesterMulai = $hariIni->month >= 7 ? $hariIni->setDate($hariIni->year, 7, 1) : $hariIni->setDate($hariIni->year, 1, 1);
        $semesterSelesai = $hariIni->month >= 7 ? $hariIni->setDate($hariIni->year, 12, 31) : $hariIni->setDate($hariIni->year, 6, 30);

        [$rekapMulai, $rekapSelesai, $rekapFilterLabel] = match ($filterRekap) {
            'mingguan' => [
                $hariIni->startOfWeek(),
                $hariIni->endOfWeek(),
                'Minggu Ini ('.\App\Support\Tanggal::pendek($hariIni->startOfWeek()).' - '.\App\Support\Tanggal::pendek($hariIni->endOfWeek()).')',
            ],
            'bulanan' => [
                $bulanTerpilihDate->startOfMonth(),
                $bulanTerpilihDate->endOfMonth(),
                'Bulan '.\App\Support\Tanggal::bulanTahun($bulanTerpilihDate),
            ],
            'semester' => [
                $semesterMulai,
                $semesterSelesai,
                'Semester Ini ('.$namaSemester.' '.$hariIni->year.')',
            ],
            default => [
                $hariIni,
                $hariIni,
                'Hari Ini ('.\App\Support\Tanggal::panjang($hariIni).')',
            ],
        };

        $rekapPeriodeRingkasan = $filterRekap !== 'hari_ini'
            ? $this->rekap->ringkasanPeriode($rekapMulai, $rekapSelesai)
            : null;
        $rekapHariEfektifCount = $filterRekap !== 'hari_ini'
            ? count($this->rekap->hariEfektif($rekapMulai, $rekapSelesai))
            : null;

        // Paginasi siswa per 10 orang dengan pencarian opsional
        $cariSiswa = trim((string) $request->input('cari'));
        $daftarSiswa = Student::aktif()
            ->when($cariSiswa !== '', fn ($q) => $q->where('nama', 'like', '%'.$cariSiswa.'%'))
            ->urut()
            ->paginate(10)
            ->withQueryString();

        $studentIds = $daftarSiswa->pluck('id')->all();

        // Data rekap absensi umum per siswa
        $rekapUmumSiswa = [];
        if (! empty($studentIds)) {
            $rawUmum = \Illuminate\Support\Facades\DB::table('daily_attendances')
                ->whereIn('student_id', $studentIds)
                ->whereBetween('tanggal', [$rekapMulai->toDateString(), $rekapSelesai->toDateString()])
                ->whereNotIn('tanggal', \Illuminate\Support\Facades\DB::table('holidays')->whereBetween('tanggal', [$rekapMulai->toDateString(), $rekapSelesai->toDateString()])->select('tanggal'))
                ->select('student_id', 'status', 'keterangan')
                ->get();

            // Ambil status presensi hari ini secara spesifik agar selalu akurat (kosong jika hari libur)
            $todayUmum = $liburHariIni
                ? collect()
                : \Illuminate\Support\Facades\DB::table('daily_attendances')
                    ->whereIn('student_id', $studentIds)
                    ->whereDate('tanggal', $hariIni->toDateString())
                    ->get()
                    ->keyBy('student_id');

            foreach ($studentIds as $sid) {
                $rows = $rawUmum->where('student_id', $sid);
                $counts = RekapService::hitunganKosong();
                foreach ($rows as $r) {
                    if (isset($counts[$r->status])) {
                        $counts[$r->status]++;
                    }
                }
                $total = array_sum($counts);
                $persen = RekapService::persen($counts['hadir'] + $counts['dispensasi'], $total);
                $todayRow = $todayUmum->get($sid);

                $rekapUmumSiswa[$sid] = (object) [
                    'counts' => $counts,
                    'total' => $total,
                    'persen' => $persen,
                    'status_hari_ini' => $todayRow?->status,
                    'keterangan_hari_ini' => $todayRow?->keterangan,
                ];
            }
        }

        // Data rekap absensi mapel per siswa
        $rekapMapelSiswa = [];
        if (! empty($studentIds)) {
            $rawMapel = \Illuminate\Support\Facades\DB::table('subject_attendances')
                ->whereIn('student_id', $studentIds)
                ->whereBetween('tanggal', [$rekapMulai->toDateString(), $rekapSelesai->toDateString()])
                ->whereNotIn('tanggal', \Illuminate\Support\Facades\DB::table('holidays')->whereBetween('tanggal', [$rekapMulai->toDateString(), $rekapSelesai->toDateString()])->select('tanggal'))
                ->select('student_id', 'status')
                ->get();

            foreach ($studentIds as $sid) {
                $rows = $rawMapel->where('student_id', $sid);
                $hadir = $rows->whereIn('status', ['hadir', 'dispensasi'])->count();
                $total = $rows->count();
                $persen = RekapService::persen($hadir, $total);

                $rekapMapelSiswa[$sid] = (object) [
                    'hadir' => $hadir,
                    'total' => $total,
                    'persen' => $persen,
                ];
            }
        }

        return view('publik.index', [
            'hariIni' => $hariIni,
            'liburHariIni' => $liburHariIni,
            'ringkasan' => $this->rekap->ringkasanHarian($hariIni),
            'tidakHadir' => $this->rekap->tidakHadirPada($hariIni),
            'periodeLabel' => $labelGrafik,
            'periodePilihan' => $pilihanGrafik,
            'komposisi' => $this->rekap->ringkasanPeriode($mulaiGrafik, $selesaiGrafik),
            'tren' => $this->rekap->trenHarian($mulaiGrafik, $selesaiGrafik),
            'perMapel' => $this->rekap->persenPerMapel($mulaiGrafik, $selesaiGrafik),
            'daftarSiswa' => $daftarSiswa,
            'rekapUmumSiswa' => $rekapUmumSiswa,
            'rekapMapelSiswa' => $rekapMapelSiswa,
            'filterRekap' => $filterRekap,
            'rekapFilterLabel' => $rekapFilterLabel,
            'rekapPeriodeRingkasan' => $rekapPeriodeRingkasan,
            'rekapHariEfektifCount' => $rekapHariEfektifCount,
            'cariSiswa' => $cariSiswa,
            'daftarBulan' => $daftarBulan,
            'selectedBulan' => $selectedBulan,
            'selectedTahun' => $selectedTahun,
            'bulanTerpilihDate' => $bulanTerpilihDate,
        ]);
    }

    /** Pencarian siswa: mengembalikan daftar nama + no absen saja. */
    public function cariSiswa(Request $request): JsonResponse
    {
        $kata = trim((string) $request->input('q'));

        if (mb_strlen($kata) < 2) {
            return response()->json(['data' => []]);
        }

        $hasil = Student::aktif()
            ->where('nama', 'like', '%'.$kata.'%')
            ->urut()
            ->limit(10)
            ->get(['id', 'nama', 'no_absen'])
            ->map(fn ($s) => [
                'id' => $s->id,
                'nama' => $s->nama,
                'no_absen' => $s->no_absen,
                'url' => route('publik.siswa', $s->id),
            ]);

        return response()->json(['data' => $hasil]);
    }

    public function ringkasanSiswa(Request $request, Student $siswa): View
    {
        abort_unless($siswa->is_active, 404);

        $hariIni = CarbonImmutable::today();
        $selectedBulan = (int) $request->input('bulan', $hariIni->month);
        $selectedTahun = (int) $request->input('tahun', $hariIni->year);
        if ($request->filled('bulan_tahun')) {
            try {
                $bt = CarbonImmutable::parse($request->input('bulan_tahun').'-01');
                $selectedBulan = $bt->month;
                $selectedTahun = $bt->year;
            } catch (\Throwable) {}
        }
        $bulanTerpilih = CarbonImmutable::createFromDate($selectedTahun, $selectedBulan, 1)->startOfDay();
        [$mulai, $selesai, $labelPeriode, $pilihan] = $this->periode($request, $hariIni, $bulanTerpilih);

        $umum = $this->rekap->perSiswa($mulai, $selesai)->firstWhere('siswa.id', $siswa->id);

        return view('publik.siswa', [
            'siswa' => $siswa,
            'umum' => $umum,
            'perMapel' => $this->rekap->mapelUntukSiswa($siswa->id, $mulai, $selesai),
            'periodeLabel' => $labelPeriode,
            'periodePilihan' => $pilihan,
            'daftarBulan' => \App\Support\Periode::daftarBulan(),
            'selectedBulan' => $selectedBulan,
            'selectedTahun' => $selectedTahun,
        ]);
    }

    /** Endpoint agregat untuk Chart.js — read-only, tanpa data pribadi. */
    public function grafik(Request $request): JsonResponse
    {
        $hariIni = CarbonImmutable::today();
        $selectedBulan = (int) $request->input('bulan', $hariIni->month);
        $selectedTahun = (int) $request->input('tahun', $hariIni->year);
        if ($request->filled('bulan_tahun')) {
            try {
                $bt = CarbonImmutable::parse($request->input('bulan_tahun').'-01');
                $selectedBulan = $bt->month;
                $selectedTahun = $bt->year;
            } catch (\Throwable) {}
        }
        $bulanTerpilih = CarbonImmutable::createFromDate($selectedTahun, $selectedBulan, 1)->startOfDay();
        [$mulai, $selesai, $labelPeriode] = $this->periode($request, $hariIni, $bulanTerpilih);

        $komposisi = $this->rekap->ringkasanPeriode($mulai, $selesai);
        $tren = $this->rekap->trenHarian($mulai, $selesai);
        $mapel = $this->rekap->persenPerMapel($mulai, $selesai);

        return response()->json([
            'label_periode' => $labelPeriode,
            'komposisi' => [
                'labels' => ['Hadir', 'Sakit', 'Izin', 'Alpa', 'Dispensasi'],
                'data' => [
                    $komposisi['hadir'], $komposisi['sakit'], $komposisi['izin'],
                    $komposisi['alpa'], $komposisi['dispensasi'],
                ],
                'total' => $komposisi['total'],
                'persen' => $komposisi['persen'],
            ],
            'tren' => $tren,
            'mapel' => [
                'labels' => $mapel->pluck('singkatan'),
                'nama' => $mapel->pluck('nama'),
                'data' => $mapel->pluck('persen'),
                'warna' => $mapel->pluck('warna'),
            ],
        ]);
    }

    /**
     * Pemilih periode untuk publik: bulan pilihan / bulan lalu / semester.
     *
     * @return array{0:CarbonImmutable,1:CarbonImmutable,2:string,3:string}
     */
    private function periode(Request $request, CarbonImmutable $hariIni, ?CarbonImmutable $bulanTerpilih = null): array
    {
        $bulanTerpilih = $bulanTerpilih ?? $hariIni;
        $defaultPeriode = $request->input('filter_rekap') === 'semester' ? 'semester' : 'bulanan';
        $pilihan = $request->input('periode', $defaultPeriode);

        return match ($pilihan) {
            'bulan_lalu' => [
                $hariIni->subMonthNoOverflow()->startOfMonth(),
                $hariIni->subMonthNoOverflow()->endOfMonth(),
                'Bulan '.\App\Support\Tanggal::bulanTahun($hariIni->subMonthNoOverflow()),
                'bulan_lalu',
            ],
            'semester' => $hariIni->month >= 7
                ? [$hariIni->setDate($hariIni->year, 7, 1), $hariIni->setDate($hariIni->year, 12, 31), 'Semester Ganjil '.$hariIni->year, 'semester']
                : [$hariIni->setDate($hariIni->year, 1, 1), $hariIni->setDate($hariIni->year, 6, 30), 'Semester Genap '.$hariIni->year, 'semester'],
            default => [
                $bulanTerpilih->startOfMonth(),
                $bulanTerpilih->endOfMonth(),
                'Bulan '.\App\Support\Tanggal::bulanTahun($bulanTerpilih),
                'bulanan',
            ],
        };
    }
}
