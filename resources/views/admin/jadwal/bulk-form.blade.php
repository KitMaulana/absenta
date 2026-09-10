@extends('layouts.admin')
@section('title', 'Tambah Jadwal Massal (Rentang JP)')
@section('subtitle', 'Tambahkan jam pelajaran berurutan sekaligus untuk satu mata pelajaran (misal JP 1 s/d 3)')

@section('content')
<div class="max-w-2xl">
    <x-card judul="Form Input Massal Rentang Jam Pelajaran">
        <form method="POST" action="{{ route('admin.jadwal.bulk.store') }}" class="space-y-5">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="hari" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Hari <span class="text-rose-500">*</span></label>
                    <select id="hari" name="hari" required
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($hariList as $kode => $label)
                            <option value="{{ $kode }}" @selected(old('hari', 'senin') === $kode)>
                                {{ $label }} {{ in_array($kode, $hariAktif, true) ? '(Hari Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('hari') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="subject_id" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <select id="subject_id" name="subject_id" required
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach ($mapel as $m)
                            <option value="{{ $m->id }}" @selected(old('subject_id') == $m->id)>
                                {{ $m->nama }} ({{ $m->singkatan }})
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="guru_pengampu" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Guru Pengampu</label>
                <input id="guru_pengampu" name="guru_pengampu" type="text" value="{{ old('guru_pengampu') }}"
                       placeholder="Contoh: Pak Bangkit, S.Pd."
                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                @error('guru_pengampu') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-4 space-y-4">
                <div class="flex items-center gap-2">
                    <span class="grid h-6 w-6 place-items-center rounded-lg bg-indigo-600 text-xs font-black text-white">#</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-900">Rentang Jam Pelajaran (JP)</h3>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="jam_ke_mulai" class="block text-xs font-bold text-slate-700 mb-1">Dari JP Ke- <span class="text-rose-500">*</span></label>
                        <input id="jam_ke_mulai" name="jam_ke_mulai" type="number" min="1" max="15" value="{{ old('jam_ke_mulai', 1) }}" required
                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-900 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                        @error('jam_ke_mulai') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="jam_ke_selesai" class="block text-xs font-bold text-slate-700 mb-1">Sampai JP Ke- <span class="text-rose-500">*</span></label>
                        <input id="jam_ke_selesai" name="jam_ke_selesai" type="number" min="1" max="15" value="{{ old('jam_ke_selesai', 3) }}" required
                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-900 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                        @error('jam_ke_selesai') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <p class="text-[11px] text-indigo-700">
                    Sistem akan membuat seluruh JP di antara nilai awal dan akhir. Misal: JP 1 sampai JP 3 akan membuat 3 jadwal (JP 1, JP 2, dan JP 3).
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="jam_mulai" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Jam Mulai Sesi</label>
                    <input id="jam_mulai" name="jam_mulai" type="time" value="{{ old('jam_mulai') }}"
                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                    @error('jam_mulai') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="jam_selesai" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Jam Selesai Sesi</label>
                    <input id="jam_selesai" name="jam_selesai" type="time" value="{{ old('jam_selesai') }}"
                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                    @error('jam_selesai') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-2.5 border-t border-slate-100 pt-4">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Sekaligus</span>
                </button>
                <a href="{{ route('admin.jadwal.index') }}"
                   class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </x-card>
</div>
@endsection
