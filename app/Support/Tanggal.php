<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Pemformat tanggal Indonesia yang tidak bergantung pada ekstensi intl,
 * karena XAMPP bawaan sering tidak mengaktifkannya.
 */
class Tanggal
{
    public const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public const BULAN = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    /** Contoh: "Senin, 1 September 2026" */
    public static function panjang(CarbonInterface $d): string
    {
        return self::HARI[(int) $d->format('w')].', '.self::pendek($d);
    }

    /** Contoh: "1 September 2026" */
    public static function pendek(CarbonInterface $d): string
    {
        return $d->day.' '.self::BULAN[$d->month].' '.$d->year;
    }

    /** Contoh: "September 2026" */
    public static function bulanTahun(CarbonInterface $d): string
    {
        return self::BULAN[$d->month].' '.$d->year;
    }

    public static function namaHari(CarbonInterface $d): string
    {
        return self::HARI[(int) $d->format('w')];
    }
}
