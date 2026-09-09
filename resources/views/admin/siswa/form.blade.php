@extends('layouts.admin')
@section('title', $siswa->exists ? 'Edit Siswa' : 'Tambah Siswa')
@section('subtitle', $siswa->exists ? 'Perbarui data identitas siswa' : 'Tambahkan siswa baru ke dalam kelas')

@section('content')
<x-card class="max-w-2xl" judul="{{ $siswa->exists ? 'Formulir Edit Siswa' : 'Formulir Tambah Siswa' }}">
    <form method="POST" action="{{ $siswa->exists ? route('admin.siswa.update', $siswa) : route('admin.siswa.store') }}" class="space-y-5">
        @csrf
        @if ($siswa->exists) @method('PUT') @endif

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="no_absen" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">No. Absen</label>
                <input id="no_absen" name="no_absen" type="number" min="1" value="{{ old('no_absen', $siswa->no_absen) }}" required
                       class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="sm:col-span-2">
                <label for="nama" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Nama Lengkap Siswa</label>
                <input id="nama" name="nama" value="{{ old('nama', $siswa->nama) }}" required placeholder="Contoh: Ahmad Fauzan"
                       class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="nisn" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">NISN <span class="font-normal text-slate-400">(Opsional)</span></label>
                <input id="nisn" name="nisn" value="{{ old('nisn', $siswa->nisn) }}" placeholder="10 digit nomor NISN"
                       class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                <p class="mt-1 text-[11px] text-slate-400">Privat: tidak akan ditampilkan di halaman infografis publik.</p>
            </div>
            <div>
                <label for="jenis_kelamin" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Jenis Kelamin</label>
                <select id="jenis_kelamin" name="jenis_kelamin" class="w-full rounded-xl border-slate-200 text-sm font-semibold shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="L" @selected(old('jenis_kelamin', $siswa->jenis_kelamin) === 'L')>Laki-laki (L)</option>
                    <option value="P" @selected(old('jenis_kelamin', $siswa->jenis_kelamin) === 'P')>Perempuan (P)</option>
                </select>
            </div>
        </div>

        <div>
            <label for="no_hp_ortu" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">No. Kontak / HP Orang Tua <span class="font-normal text-slate-400">(Opsional)</span></label>
            <input id="no_hp_ortu" name="no_hp_ortu" value="{{ old('no_hp_ortu', $siswa->no_hp_ortu) }}" placeholder="08xxxxxxxxxx"
                   class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
            <p class="mt-1 text-[11px] text-slate-400">Privat: hanya dapat dilihat oleh admin/wali kelas.</p>
        </div>

        <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3">
            <label class="flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-slate-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $siswa->is_active ?? true))
                       class="rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span>Status Aktif (Siswa tercatat dalam absensi dan perhitungan kelas)</span>
            </label>
        </div>

        <div class="flex items-center gap-2.5 border-t border-slate-100 pt-4">
            <button type="submit"
                    class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                Simpan Data Siswa
            </button>
            <a href="{{ route('admin.siswa.index') }}"
               class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                Batal
            </a>
        </div>
    </form>
</x-card>
@endsection
