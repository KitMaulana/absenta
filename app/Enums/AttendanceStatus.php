<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Hadir = 'hadir';
    case Sakit = 'sakit';
    case Izin = 'izin';
    case Alpa = 'alpa';
    case Dispensasi = 'dispensasi';

    /** Kode singkat untuk tabel rekap & PDF. */
    public function kode(): string
    {
        return match ($this) {
            self::Hadir => 'H',
            self::Sakit => 'S',
            self::Izin => 'I',
            self::Alpa => 'A',
            self::Dispensasi => 'D',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Hadir => 'Hadir',
            self::Sakit => 'Sakit',
            self::Izin => 'Izin',
            self::Alpa => 'Alpa',
            self::Dispensasi => 'Dispensasi',
        };
    }

    /** Warna hex dipakai bersama oleh badge Tailwind dan Chart.js. */
    public function warna(): string
    {
        return match ($this) {
            self::Hadir => '#16a34a',
            self::Sakit => '#f59e0b',
            self::Izin => '#3b82f6',
            self::Alpa => '#dc2626',
            self::Dispensasi => '#8b5cf6',
        };
    }

    /** Kelas tombol radio pada form input absensi (state terpilih). */
    public function peerClass(): string
    {
        return match ($this) {
            self::Hadir => 'peer-checked:bg-green-600 peer-checked:border-green-600',
            self::Sakit => 'peer-checked:bg-amber-500 peer-checked:border-amber-500',
            self::Izin => 'peer-checked:bg-blue-600 peer-checked:border-blue-600',
            self::Alpa => 'peer-checked:bg-red-600 peer-checked:border-red-600',
            self::Dispensasi => 'peer-checked:bg-violet-600 peer-checked:border-violet-600',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Hadir => 'bg-green-100 text-green-800 ring-green-600/20',
            self::Sakit => 'bg-amber-100 text-amber-800 ring-amber-600/20',
            self::Izin => 'bg-blue-100 text-blue-800 ring-blue-600/20',
            self::Alpa => 'bg-red-100 text-red-800 ring-red-600/20',
            self::Dispensasi => 'bg-violet-100 text-violet-800 ring-violet-600/20',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }

    /** @return array<int,string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Status selain hadir yang harus dipilih ketika checkbox tidak dicentang. */
    public static function tidakHadir(): array
    {
        return [self::Sakit, self::Izin, self::Alpa, self::Dispensasi];
    }
}
