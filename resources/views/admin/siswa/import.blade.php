@extends('layouts.admin')
@section('title', 'Import Siswa dari CSV')
@section('subtitle', 'Unggah data siswa sekaligus menggunakan format file spreadsheet CSV')

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <x-card class="lg:col-span-2" judul="Unggah Berkas CSV">
        <form method="POST" action="{{ route('admin.siswa.import') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label for="file" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Pilih Berkas CSV</label>
                <input id="file" name="file" type="file" accept=".csv,text/csv" required
                       class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2 text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-white hover:file:bg-slate-800">
                <p class="mt-1 text-[11px] text-slate-400">Ukuran berkas maksimal 2 MB. Pastikan encoding UTF-8 dengan pemisah koma (,).</p>
            </div>

            <fieldset class="space-y-2.5">
                <legend class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Metode Impor</legend>
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3.5 text-xs cursor-pointer hover:bg-slate-50/70 transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/40">
                    <input type="radio" name="mode" value="tambah" checked class="mt-0.5 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <strong class="text-slate-900 font-bold block">Tambah / Perbarui Otomatis</strong>
                        <span class="text-slate-500 mt-0.5 block leading-relaxed">Siswa dicocokkan berdasarkan nama. Siswa yang belum ada akan ditambahkan, yang sudah ada diperbarui datanya. Siswa lain tidak akan diubah.</span>
                    </div>
                </label>
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3.5 text-xs cursor-pointer hover:bg-slate-50/70 transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/40">
                    <input type="radio" name="mode" value="ganti" class="mt-0.5 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <strong class="text-slate-900 font-bold block">Ganti Seluruh Daftar Kelas</strong>
                        <span class="text-slate-500 mt-0.5 block leading-relaxed">Semua siswa saat ini akan dinonaktifkan terlebih dahulu, kemudian seluruh siswa dari berkas CSV akan diaktifkan. Siswa tanpa riwayat absensi akan dihapus.</span>
                    </div>
                </label>
            </fieldset>

            <div class="flex items-center gap-2.5 border-t border-slate-100 pt-4">
                <button type="submit"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                    Mulai Proses Import
                </button>
                <a href="{{ route('admin.siswa.index') }}"
                   class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </x-card>

    <x-card judul="Format Kolom CSV">
        <p class="text-xs text-slate-600">Baris pertama dokumen CSV harus memuat nama header berikut:</p>
        <ul class="mt-2.5 space-y-1.5 text-xs">
            <li class="flex items-center gap-2"><code class="rounded-md bg-slate-100 px-2 py-0.5 font-bold text-slate-800 text-[11px]">no_absen</code> <span class="text-slate-500">&mdash; Nomor urut (angka)</span></li>
            <li class="flex items-center gap-2"><code class="rounded-md bg-slate-100 px-2 py-0.5 font-bold text-slate-800 text-[11px]">nama</code> <span class="text-slate-500">&mdash; Nama lengkap siswa</span></li>
            <li class="flex items-center gap-2"><code class="rounded-md bg-slate-100 px-2 py-0.5 font-bold text-slate-800 text-[11px]">jenis_kelamin</code> <span class="text-slate-500">&mdash; <code>L</code> atau <code>P</code></span></li>
        </ul>
        <p class="mt-3 text-xs text-slate-500">Kolom opsional tambahan: <code class="rounded bg-slate-100 px-1 py-0.5 text-[11px]">nisn</code>, <code class="rounded bg-slate-100 px-1 py-0.5 text-[11px]">no_hp_ortu</code>.</p>

        <p class="mt-4 text-[11px] font-bold uppercase tracking-wider text-slate-400">Contoh Isi CSV</p>
        <pre class="mt-1.5 overflow-x-auto rounded-xl bg-slate-950 p-3.5 text-[11px] leading-relaxed text-slate-200 border border-slate-800">no_absen,nama,nisn,jenis_kelamin
1,Ahmad Fauzan,0012345678,L
2,Alya Rahmawati,0012345679,P</pre>

        <a href="{{ route('admin.siswa.export') }}" class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-700 transition">
            <span>Unduh data saat ini sebagai contoh template</span>
            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
        </a>
    </x-card>
</div>
@endsection
