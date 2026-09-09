<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const DEFAULTS = [
        'nama_kelas' => 'XII IPA 3',
        'nama_sekolah' => 'SMA Negeri 1 Contoh',
        'nama_wali_kelas' => 'Budi Utama, S.Pd.',
        'nip_wali_kelas' => '',
        'tahun_ajaran' => '2026/2027',
        'semester' => 'Ganjil',
        'logo' => '',
        'hari_aktif' => '["senin","selasa","rabu","kamis","jumat"]',
    ];

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget('settings.all');
        static::saved($flush);
        static::deleted($flush);
    }

    /** @return array<string,string> */
    public static function map(): array
    {
        return Cache::rememberForever('settings.all', function () {
            try {
                return array_merge(self::DEFAULTS, self::query()->pluck('value', 'key')->all());
            } catch (\Throwable) {
                // Tabel belum ada (instalasi baru, sebelum migrate).
                return self::DEFAULTS;
            }
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::map()[$key] ?? $default ?? (self::DEFAULTS[$key] ?? null);
    }

    /** hari_aktif disimpan sebagai JSON, selalu kembalikan array. */
    public static function hariAktif(): array
    {
        $raw = self::get('hari_aktif');
        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);

        return is_array($decoded) && $decoded !== []
            ? $decoded
            : json_decode(self::DEFAULTS['hari_aktif'], true);
    }

    public static function put(string $key, mixed $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode($value) : $value]);
    }
}
