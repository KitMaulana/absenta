@extends('layouts.admin')
@section('title', 'Import Jadwal Pelajaran')
@section('subtitle', 'Unggah jadwal pelajaran sekaligus menggunakan berkas spreadsheet CSV seperti di SIDACHEERS')

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <x-card class="lg:col-span-2" judul="Unggah Berkas CSV Jadwal">
        <form method="POST" action="{{ route('admin.jadwal.import') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label for="file" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Pilih Berkas CSV <span class="text-rose-500">*</span></label>
                <input id="file" name="file" type="file" accept=".csv,text/csv,text/plain" required
                       class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2 text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-white hover:file:bg-slate-800">
                <p class="mt-1 text-[11px] text-slate-400">Ukuran berkas maksimal 5 MB. Mendukung pemisah koma (,) atau titik koma (;).</p>
            </div>

            <fieldset class="space-y-2.5">
                <legend class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Metode Impor</legend>
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3.5 text-xs cursor-pointer hover:bg-slate-50/70 transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/40">
                    <input type="radio" name="mode" value="tambah" checked class="mt-0.5 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <strong class="text-slate-900 font-bold block">Tambah / Perbarui Jadwal (Upsert)</strong>
                        <span class="text-slate-500 mt-0.5 block leading-relaxed">Jadwal pada hari dan jam_ke yang sama akan diperbarui datanya. Jam pelajaran baru akan ditambahkan tanpa menghapus jadwal hari lain.</span>
                    </div>
                </label>
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3.5 text-xs cursor-pointer hover:bg-slate-50/70 transition has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/40">
                    <input type="radio" name="mode" value="ganti" class="mt-0.5 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <strong class="text-slate-900 font-bold block">Ganti Seluruh Jadwal</strong>
                        <span class="text-slate-500 mt-0.5 block leading-relaxed">Seluruh jadwal yang belum memiliki riwayat absensi akan dihapus, kemudian diisi ulang sesuai daftar dalam berkas CSV.</span>
                    </div>
                </label>
            </fieldset>

            <div class="flex items-center gap-2.5 border-t border-slate-100 pt-4">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Import Sekarang</span>
                </button>
                <a href="{{ route('admin.jadwal.template') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download Template CSV</span>
                </a>
                <a href="{{ route('admin.jadwal.index') }}"
                   class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </x-card>

    <div class="space-y-4">
        <x-card judul="Format Kolom CSV">
            <p class="text-xs text-slate-600">Berkas CSV harus memuat baris header berikut:</p>
            <div class="mt-2.5 overflow-x-auto rounded-xl border border-slate-100">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50 font-bold text-slate-600">
                        <tr>
                            <th class="px-3 py-2 text-left">Kolom</th>
                            <th class="px-3 py-2 text-left">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        <tr>
                            <td class="px-3 py-2 font-mono font-bold text-indigo-600">hari</td>
                            <td class="px-3 py-2">Senin s.d. Minggu</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 font-mono font-bold text-indigo-600">jam_ke</td>
                            <td class="px-3 py-2">Nomor JP (1–15)</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 font-mono font-bold text-indigo-600">mata_pelajaran</td>
                            <td class="px-3 py-2">Nama mata pelajaran</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 font-mono text-slate-500">guru_pengampu</td>
                            <td class="px-3 py-2 text-slate-400">Nama guru (opsional)</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 font-mono text-slate-500">jam_mulai</td>
                            <td class="px-3 py-2 text-slate-400">Contoh: 07:00 (opsional)</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2 font-mono text-slate-500">jam_selesai</td>
                            <td class="px-3 py-2 text-slate-400">Contoh: 07:45 (opsional)</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 rounded-xl border border-sky-200 bg-sky-50/80 p-3 text-xs text-sky-900">
                <strong class="flex items-center gap-1.5 font-bold text-sky-800">
                    <svg class="h-4 w-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Auto-Create Mata Pelajaran
                </strong>
                <p class="mt-1 text-[11px] leading-relaxed text-sky-700">
                    Jika nama mata pelajaran yang diimpor belum terdaftar di database, sistem akan otomatis membuatnya dengan warna palet unik.
                </p>
            </div>
        </x-card>

        <x-card judul="Contoh Data CSV">
            <pre class="overflow-x-auto rounded-xl bg-slate-950 p-3 text-[11px] leading-relaxed text-slate-200 border border-slate-800 font-mono">hari,jam_ke,mata_pelajaran,guru_pengampu,jam_mulai,jam_selesai
Senin,1,Bahasa Indonesia,Pak Bangkit,07:00,07:45
Senin,2,Bahasa Indonesia,Pak Bangkit,07:45,08:30
Senin,3,Bahasa Indonesia,Pak Bangkit,08:30,09:15
Senin,4,Matematika,Bu Rina,09:30,10:15
Senin,5,Matematika,Bu Rina,10:15,11:00</pre>
        </x-card>
    </div>
</div>
@endsection
