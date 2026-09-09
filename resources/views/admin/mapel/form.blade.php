@extends('layouts.admin')
@section('title', $item->exists ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran')
@section('subtitle', $item->exists ? 'Perbarui informasi mata pelajaran' : 'Daftarkan mata pelajaran baru untuk jadwal kelas')

@section('content')
<x-card class="max-w-xl" judul="{{ $item->exists ? 'Formulir Edit Mapel' : 'Formulir Tambah Mapel' }}">
    <form method="POST" action="{{ $item->exists ? route('admin.mapel.update', $item) : route('admin.mapel.store') }}" class="space-y-5">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <div>
            <label for="nama" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Nama Mata Pelajaran</label>
            <input id="nama" name="nama" value="{{ old('nama', $item->nama) }}" required placeholder="Contoh: Matematika Peminatan"
                   class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="singkatan" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Singkatan / Kode Mapel</label>
                <input id="singkatan" name="singkatan" value="{{ old('singkatan', $item->singkatan) }}" required maxlength="20" placeholder="Contoh: MTK"
                       class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                <p class="mt-1 text-[11px] text-slate-400">Digunakan pada sumbu grafik dan pill jadwal.</p>
            </div>
            <div>
                <label for="warna" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Warna Visual Grafik</label>
                <div class="flex items-center gap-3">
                    <input id="warna" name="warna" type="color" value="{{ old('warna', $item->warna ?: '#3b82f6') }}"
                           class="h-10 w-16 cursor-pointer rounded-xl border border-slate-200 p-1 shadow-2xs">
                    <span class="text-xs text-slate-500 leading-tight">Pilih warna unik agar mudah dibedakan di diagram.</span>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3">
            <label class="flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-slate-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))
                       class="rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span>Status Mapel Aktif (Dapat dipilih saat menyusun jadwal pelajaran)</span>
            </label>
        </div>

        <div class="flex items-center gap-2.5 border-t border-slate-100 pt-4">
            <button type="submit"
                    class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                Simpan Mata Pelajaran
            </button>
            <a href="{{ route('admin.mapel.index') }}"
               class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                Batal
            </a>
        </div>
    </form>
</x-card>
@endsection
