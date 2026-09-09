@extends('layouts.admin')
@section('title', 'Pengaturan Kelas')
@section('subtitle', 'Identitas kelas ini digunakan pada header publik, sidebar admin, dan kop laporan resmi PDF')

@section('content')
<form method="POST" action="{{ route('admin.pengaturan.update') }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-3">
    @csrf @method('PUT')

    <x-card class="lg:col-span-2" judul="Identitas &amp; Kalender Kelas">
        <div class="space-y-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="nama_kelas" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Nama Kelas</label>
                    <input id="nama_kelas" name="nama_kelas" value="{{ old('nama_kelas', $nilai['nama_kelas']) }}" required placeholder="Contoh: XII MIPA 1"
                           class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="nama_sekolah" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Nama Sekolah / Instansi <span class="font-normal text-slate-400">(Opsional)</span></label>
                    <input id="nama_sekolah" name="nama_sekolah" value="{{ old('nama_sekolah', $nilai['nama_sekolah']) }}" placeholder="Contoh: SMAN 1 Jakarta"
                           class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="nama_wali_kelas" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Nama Wali Kelas</label>
                    <input id="nama_wali_kelas" name="nama_wali_kelas" value="{{ old('nama_wali_kelas', $nilai['nama_wali_kelas']) }}" required placeholder="Nama dan gelar wali kelas"
                           class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="nip_wali_kelas" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">NIP Wali Kelas <span class="font-normal text-slate-400">(Opsional)</span></label>
                    <input id="nip_wali_kelas" name="nip_wali_kelas" value="{{ old('nip_wali_kelas', $nilai['nip_wali_kelas']) }}" placeholder="Nomor Induk Pegawai"
                           class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-[11px] text-slate-400">Dicantumkan di bawah tanda tangan laporan bulanan.</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="tahun_ajaran" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Tahun Ajaran</label>
                    <input id="tahun_ajaran" name="tahun_ajaran" value="{{ old('tahun_ajaran', $nilai['tahun_ajaran']) }}" required placeholder="Contoh: 2026/2027"
                           class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="semester" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Semester</label>
                    <select id="semester" name="semester" class="w-full rounded-xl border-slate-200 text-sm font-semibold shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (['Ganjil', 'Genap'] as $s)
                            <option value="{{ $s }}" @selected(old('semester', $nilai['semester']) === $s)>Semester {{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <fieldset class="border-t border-slate-100 pt-5">
                <legend class="text-xs font-bold uppercase tracking-wider text-slate-500">Hari Aktif Sekolah</legend>
                <p class="text-xs text-slate-400 mt-0.5">Hari yang dicentang akan dihitung sebagai hari efektif dalam persentase rasio presensi.</p>
                <div class="mt-3.5 flex flex-wrap gap-2.5">
                    @foreach (\App\Models\Schedule::HARI as $kode => $label)
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/60 has-[:checked]:text-indigo-900">
                            <input type="checkbox" name="hari_aktif[]" value="{{ $kode }}"
                                   @checked(in_array($kode, old('hari_aktif', $hariAktif), true))
                                   class="rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </fieldset>
        </div>
    </x-card>

    {{-- Kolom Kanan: Logo & Simpan --}}
    <div class="space-y-6">
        <x-card judul="Logo Kelas / Sekolah">
            <div class="text-center">
                @if (! empty($nilai['logo']))
                    <div class="mx-auto h-28 w-28 rounded-2xl border border-slate-200 bg-white p-2 shadow-xs">
                        <img src="{{ asset('storage/'.$nilai['logo']) }}" alt="Logo saat ini" class="h-full w-full object-contain">
                    </div>
                    <label class="mt-3.5 inline-flex items-center gap-2 text-xs font-semibold text-rose-600 cursor-pointer hover:text-rose-700">
                        <input type="checkbox" name="hapus_logo" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        Hapus logo saat ini
                    </label>
                @else
                    <div class="mx-auto grid h-24 w-24 place-items-center rounded-2xl bg-slate-100 text-3xl text-slate-400 border border-slate-200/80">
                        🏫
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Belum ada logo terpasang.</p>
                @endif
            </div>

            <div class="mt-5 border-t border-slate-100 pt-4">
                <label for="logo" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Unggah Logo Baru</label>
                <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2 text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-white hover:file:bg-slate-800">
                <p class="mt-1 text-[11px] text-slate-400">Format PNG, JPG, atau WebP (maks. 1 MB). Akan ditampilkan di sidebar, portal publik, dan kop PDF.</p>
            </div>
        </x-card>

        <x-card>
            <button type="submit"
                    class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 py-3 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:from-indigo-500 hover:to-indigo-600 transition">
                Simpan Perubahan Pengaturan
            </button>
            <a href="{{ route('admin.dashboard') }}"
               class="mt-2.5 block rounded-xl border border-slate-200 bg-white py-2 text-center text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                Batal
            </a>
        </x-card>
    </div>
</form>
@endsection
