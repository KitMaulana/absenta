<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.pengaturan.edit', [
            'nilai' => Setting::map(),
            'hariAktif' => Setting::hariAktif(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:60'],
            'nama_sekolah' => ['nullable', 'string', 'max:120'],
            'nama_wali_kelas' => ['required', 'string', 'max:100'],
            'nip_wali_kelas' => ['nullable', 'string', 'max:40'],
            'tahun_ajaran' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
            'hari_aktif' => ['required', 'array', 'min:1'],
            'hari_aktif.*' => [Rule::in(array_keys(Schedule::HARI))],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'hapus_logo' => ['nullable', 'boolean'],
        ], [], ['hari_aktif' => 'hari aktif']);

        $logoLama = Setting::get('logo');

        foreach (['nama_kelas', 'nama_sekolah', 'nama_wali_kelas', 'nip_wali_kelas', 'tahun_ajaran', 'semester'] as $key) {
            Setting::put($key, $data[$key] ?? '');
        }

        Setting::put('hari_aktif', array_values($data['hari_aktif']));

        if ($request->boolean('hapus_logo') && $logoLama) {
            Storage::disk('public')->delete($logoLama);
            Setting::put('logo', '');
        }

        if ($request->hasFile('logo')) {
            if ($logoLama) {
                Storage::disk('public')->delete($logoLama);
            }
            Setting::put('logo', $request->file('logo')->store('logo', 'public'));
        }

        return redirect()->route('admin.pengaturan.edit')->with('sukses', 'Pengaturan kelas berhasil disimpan.');
    }
}
