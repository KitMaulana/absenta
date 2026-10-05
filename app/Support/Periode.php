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
            return Tanggal::bulanTahun($this->mulai);
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

    /** @return array<string,string> query string untuk mempertahankan filter antar-link */
    public function query(): array
    {
        return [
            'periode' => $this->jenis,
            'mulai' => $this->mulai->toDateString(),
            'selesai' => $this->selesai->toDateString(),
            'acuan' => $this->mulai->toDateString(),
        ];
    }
}
