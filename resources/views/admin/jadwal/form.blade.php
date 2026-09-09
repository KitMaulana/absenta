@extends('layouts.admin')
@section('title', $item->exists ? 'Edit Jadwal Pelajaran' : 'Tambah Jadwal Pelajaran')
@section('subtitle', $item->exists ? 'Perbarui informasi jam pelajaran' : 'Tambahkan jam pelajaran ke dalam jadwal mingguan')

@section('content')
<x-card class="max-w-2xl" judul="{{ $item->exists ? 'Formulir Edit Jadwal' : 'Formulir Tambah Jadwal' }}">
    @if ($mapel->isEmpty())
        <x-kosong pesan="Belum ada mata pelajaran aktif. Tambahkan mata pelajaran terlebih dahulu." ikon="📘">
            <a href="{{ route('admin.mapel.create') }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                <span>Tambah Mata Pelajaran</span>
            </a>
        </x-kosong>
    @else
        <form method="POST" action="{{ $item->exists ? route('admin.jadwal.update', $item) : route('admin.jadwal.store') }}" class="space-y-5">
            @csrf
            @if ($item->exists) @method('PUT') @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="hari" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Hari Pelajaran</label>
                    <select id="hari" name="hari" class="w-full rounded-xl border-slate-200 text-sm font-semibold shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (\App\Models\Schedule::HARI as $kode => $label)
                            <option value="{{ $kode }}" @selected(old('hari', $item->hari) === $kode)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="jam_ke" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Jam Ke- (JP)</label>
                    <input id="jam_ke" name="jam_ke" type="number" min="1" max="12" value="{{ old('jam_ke', $item->jam_ke) }}" required
                           class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-[11px] text-slate-400">Jam ke- harus unik untuk hari yang sama.</p>
                </div>
            </div>

            <div>
                <label for="subject_id" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Pilih Mata Pelajaran</label>
                <select id="subject_id" name="subject_id" class="w-full rounded-xl border-slate-200 text-sm font-semibold shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($mapel as $m)
                        <option value="{{ $m->id }}" @selected((int) old('subject_id', $item->subject_id) === $m->id)>
                            {{ $m->nama }} ({{ $m->singkatan }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="guru_pengampu" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Guru Pengampu <span class="font-normal text-slate-400">(Opsional)</span></label>
                <input id="guru_pengampu" name="guru_pengampu" value="{{ old('guru_pengampu', $item->guru_pengampu) }}" placeholder="Contoh: Dra. Hj. Siti Rahma, M.Pd."
                       class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="jam_mulai" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Jam Mulai <span class="font-normal text-slate-400">(Opsional)</span></label>
                    <input id="jam_mulai" name="jam_mulai" type="time" value="{{ old('jam_mulai', $item->jam_mulai ? substr($item->jam_mulai, 0, 5) : '') }}"
                           class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="jam_selesai" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Jam Selesai <span class="font-normal text-slate-400">(Opsional)</span></label>
                    <input id="jam_selesai" name="jam_selesai" type="time" value="{{ old('jam_selesai', $item->jam_selesai ? substr($item->jam_selesai, 0, 5) : '') }}"
                           class="w-full rounded-xl border-slate-200 text-sm shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex items-center gap-2.5 border-t border-slate-100 pt-4">
                <button type="submit"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                    Simpan Jadwal
                </button>
                <a href="{{ route('admin.jadwal.index') }}"
                   class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </a>
            </div>
        </form>
    @endif
</x-card>
@endsection
