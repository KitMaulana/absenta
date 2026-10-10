<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * Menerjemahkan filter periode dari query string menjadi rentang tanggal.
 * Dipakai seragam oleh halaman rekap, PDF, dan endpoint JSON publik.
 */
class Periode
{
    public const PILIHAN = [
        'harian' => 'Harian',
        'mingguan' => 'Mingguan',
        'bulanan' => 'Bulanan',
        'semester' => 'Semester',
        'custom' => 'Rentang custom',
    ];

    public function __construct(
        public readonly string $jenis,
        public readonly CarbonImmutable $mulai,
        public readonly CarbonImmutable $selesai,
    ) {}

    public static function dariRequest(Request $request, string $default = 'bulanan'): self
    {
        $jenis = $request->input('periode', $default);
        $jenis = isset(self::PILIHAN[$jenis]) ? $jenis : $default;

        $acuan = self::tanggalAman($request->input('acuan'), CarbonImmutable::today());

        // Dukungan pemilihan bulan & tahun spesifik (misal bulan=9 & tahun=2026 atau bulan_tahun=2026-09)
        if ($request->filled('bulan') && $request->filled('tahun')) {
            try {
                $acuan = CarbonImmutable::createFromDate(
                    (int) $request->input('tahun'),
                    (int) $request->input('bulan'),
                    1
                )->startOfDay();
            } catch (\Throwable) {}
        } elseif ($request->filled('bulan_tahun')) {
            try {
                $acuan = CarbonImmutable::parse($request->input('bulan_tahun').'-01')->startOfDay();
            } catch (\Throwable) {}
        } elseif ($jenis === 'bulanan' && $request->missing('acuan')) {
            // Bila tidak ada parameter bulan, periksa apakah bulan berjalan sudah memiliki data.
            // Jika belum ada data sama sekali namun ada data di bulan sebelumnya, gunakan bulan terakhir yang ada data.
            try {
                $hariIni = CarbonImmutable::today();
                $adaBulanIni = \Illuminate\Support\Facades\DB::table('daily_attendances')
                    ->whereBetween('tanggal', [$hariIni->startOfMonth()->toDateString(), $hariIni->endOfMonth()->toDateString()])
                    ->exists();

                if (! $adaBulanIni) {
                    $maxTanggal = \Illuminate\Support\Facades\DB::table('daily_attendances')->max('tanggal');
                    if ($maxTanggal) {
                        $acuan = CarbonImmutable::parse($maxTanggal)->startOfDay();
                    }
                }
            } catch (\Throwable) {}
        }

        return match ($jenis) {
            'harian' => new self($jenis, $acuan, $acuan),
            'mingguan' => new self($jenis, $acuan->startOfWeek(), $acuan->endOfWeek()),
            'semester' => self::semester($acuan),
            'custom' => self::custom($request, $acuan),
            default => new self('bulanan', $acuan->startOfMonth(), $acuan->endOfMonth()),
        };
    }

    /** Ganjil = Juli–Desember, Genap = Januari–Juni. */
    private static function semester(CarbonImmutable $acuan): self
    {
        return $acuan->month >= 7
            ? new self('semester', $acuan->setDate($acuan->year, 7, 1), $acuan->setDate($acuan->year, 12, 31))
            : new self('semester', $acuan->setDate($acuan->year, 1, 1), $acuan->setDate($acuan->year, 6, 30));
    }

    private static function custom(Request $request, CarbonImmutable $acuan): self
    {
        $mulai = self::tanggalAman($request->input('mulai'), $acuan->startOfMonth());
        $selesai = self::tanggalAman($request->input('selesai'), $acuan);

        return $selesai->lt($mulai)
            ? new self('custom', $selesai, $mulai)
            : new self('custom', $mulai, $selesai);
    }

    private static function tanggalAman(mixed $nilai, CarbonImmutable $fallback): CarbonImmutable
    {
        if (blank($nilai)) {
            return $fallback;
        }

        try {
            return CarbonImmutable::parse($nilai)->startOfDay();
        } catch (\Throwable) {
            return $fallback;
        }
    }

    public function label(): string
    {
        if ($this->mulai->isSameDay($this->selesai)) {
            return Tanggal::panjang($this->mulai);
        }

        if ($this->jenis === 'bulanan' && $this->mulai->isSameMonth($this->selesai)) {
            return 'Bulan ' . Tanggal::bulanTahun($this->mulai);
        }

        if ($this->jenis === 'semester') {
            $namaSemester = $this->mulai->month >= 7 ? 'Semester Ganjil' : 'Semester Genap';
            return "{$namaSemester} (" . Tanggal::bulanTahun($this->mulai) . ' – ' . Tanggal::bulanTahun($this->selesai) . ')';
        }

        return Tanggal::pendek($this->mulai).' – '.Tanggal::pendek($this->selesai);
    }

    /** Jumlah hari kalender dalam rentang, dipakai untuk memilih orientasi PDF. */
    public function jumlahHari(): int
    {
        return $this->mulai->diffInDays($this->selesai) + 1;
    }

    /** Bulan sebelumnya untuk navigasi cepat */
    public function bulanSebelumnya(): CarbonImmutable
    {
        return $this->mulai->subMonthNoOverflow()->startOfMonth();
    }

    /** Bulan berikutnya untuk navigasi cepat */
    public function bulanBerikutnya(): CarbonImmutable
    {
        return $this->mulai->addMonthNoOverflow()->startOfMonth();
    }

    /**
     * Menghasilkan daftar bulan pilihan (misal dalam tahun ajaran berjalan atau yang memiliki data absensi).
     *
     * @return array<int, array{bulan: int, tahun: int, kode: string, label: string, nama_bulan: string, has_data: bool}>
     */
    public static function daftarBulan(): array
    {
        $tahunAjaran = (string) \App\Models\Setting::get('tahun_ajaran', date('Y').'/'.(date('Y') + 1));
        $parts = explode('/', $tahunAjaran);
        $tahunMulai = isset($parts[0]) && is_numeric($parts[0]) ? (int) $parts[0] : (int) date('Y');
        $tahunSelesai = isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : $tahunMulai + 1;

        // Cek bulan apa saja yang memiliki data absensi di DB
        $bulanDenganData = [];
        try {
            $bulanDenganData = \Illuminate\Support\Facades\DB::table('daily_attendances')
                ->selectRaw("DISTINCT DATE_FORMAT(tanggal, '%Y-%m') as ym")
                ->pluck('ym')
                ->flip()
                ->all();
        } catch (\Throwable) {}

        $daftar = [];
        // Semester Ganjil: Juli - Desember (Tahun Mulai)
        for ($m = 7; $m <= 12; $m++) {
            $d = CarbonImmutable::createFromDate($tahunMulai, $m, 1);
            $kode = $d->format('Y-m');
            $daftar[$kode] = [
                'bulan' => $m,
                'tahun' => $tahunMulai,
                'kode' => $kode,
                'label' => Tanggal::bulanTahun($d),
                'nama_bulan' => Tanggal::BULAN[$m],
                'has_data' => isset($bulanDenganData[$kode]),
            ];
        }
        // Semester Genap: Januari - Juni (Tahun Selesai)
        for ($m = 1; $m <= 6; $m++) {
            $d = CarbonImmutable::createFromDate($tahunSelesai, $m, 1);
            $kode = $d->format('Y-m');
            $daftar[$kode] = [
                'bulan' => $m,
                'tahun' => $tahunSelesai,
                'kode' => $kode,
                'label' => Tanggal::bulanTahun($d),
                'nama_bulan' => Tanggal::BULAN[$m],
                'has_data' => isset($bulanDenganData[$kode]),
            ];
        }

        // Jika ada bulan yang memiliki data di luar rentang standar di atas, tambahkan juga
        foreach (array_keys($bulanDenganData) as $ym) {
            if (! isset($daftar[$ym])) {
                try {
                    $d = CarbonImmutable::parse($ym.'-01');
                    $daftar[$ym] = [
                        'bulan' => $d->month,
                        'tahun' => $d->year,
                        'kode' => $ym,
                        'label' => Tanggal::bulanTahun($d),
                        'nama_bulan' => Tanggal::BULAN[$d->month],
                        'has_data' => true,
                    ];
                } catch (\Throwable) {}
            }
        }

        return array_values($daftar);
    }

    /** @return array<string,string> query string untuk mempertahankan filter antar-link */
    public function query(): array
    {
        return [
            'periode' => $this->jenis,
            'mulai' => $this->mulai->toDateString(),
            'selesai' => $this->selesai->toDateString(),
            'acuan' => $this->mulai->toDateString(),
            'bulan' => (string) $this->mulai->month,
            'tahun' => (string) $this->mulai->year,
            'bulan_tahun' => $this->mulai->format('Y-m'),
        ];
    }
}
