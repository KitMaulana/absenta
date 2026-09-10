@extends('layouts.admin')
@section('title', 'Jadwal Pelajaran')
@section('subtitle', 'Jadwal jam pelajaran (JP) kelas per hari sebagai acuan presensi per mapel')

@section('content')
<div class="space-y-4" x-data="{
    terpilih: [],
    toggleSemuaHari(hari) {
        const cbs = document.querySelectorAll('.cb-hari-' + hari);
        const allChecked = Array.from(cbs).every(cb => this.terpilih.includes(cb.value));
        if (allChecked) {
            cbs.forEach(cb => {
                this.terpilih = this.terpilih.filter(id => id !== cb.value);
            });
        } else {
            cbs.forEach(cb => {
                if (!this.terpilih.includes(cb.value)) {
                    this.terpilih.push(cb.value);
                }
            });
        }
    }
}">
    {{-- Floating Action Bar jika ada jadwal terpilih --}}
    <div x-show="terpilih.length > 0" x-cloak
         class="sticky top-4 z-20 flex items-center justify-between gap-3 rounded-2xl border border-slate-800 bg-slate-900 px-5 py-3 text-white shadow-xl">
        <div class="flex items-center gap-2 text-xs font-bold">
            <span class="grid h-6 w-6 place-items-center rounded-lg bg-indigo-500 text-white font-mono" x-text="terpilih.length"></span>
            <span>Jadwal pelajaran dipilih</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="terpilih = []" class="rounded-xl bg-slate-800 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-slate-700 transition">
                Batal Pilih
            </button>
            <form method="POST" action="{{ route('admin.jadwal.bulk.destroy') }}"
                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal yang dipilih? Hanya jadwal tanpa riwayat absensi yang akan terhapus.')">
                @csrf
                <template x-for="id in terpilih" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-4 py-1.5 text-xs font-bold text-white shadow-md shadow-rose-600/25 hover:bg-rose-700 transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Hapus Massal</span>
                </button>
            </form>
        </div>
    </div>

    <x-card padat>
        <x-slot:judul>
            <div class="flex items-center gap-2">
                <span>Jadwal Pelajaran Mingguan</span>
            </div>
        </x-slot:judul>
        <x-slot:aksi>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.jadwal.import.form') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                    <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Import CSV</span>
                </a>
                <a href="{{ route('admin.jadwal.bulk.create') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-200 bg-indigo-50/60 px-3.5 py-1.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition shadow-2xs">
                    <svg class="h-3.5 w-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Tambah Massal (Rentang JP)</span>
                </a>
                <a href="{{ route('admin.jadwal.create') }}"
                   class="inline-flex items-center gap-1 rounded-xl bg-indigo-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>Tambah 1 JP</span>
                </a>
            </div>
        </x-slot:aksi>

        <div class="grid gap-px bg-slate-200/80 sm:grid-cols-2 xl:grid-cols-3">
            @foreach (\App\Models\Schedule::HARI as $kode => $label)
                @php $daftar = $perHari->get($kode, collect()); @endphp
                <div class="bg-white p-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            @if ($daftar->isNotEmpty())
                                <button type="button" @click="toggleSemuaHari('{{ $kode }}')" title="Pilih semua di hari {{ $label }}"
                                        class="rounded p-1 text-slate-400 hover:text-indigo-600 hover:bg-slate-100 transition text-[11px] font-semibold">
                                    Pilih
                                </button>
                            @endif
                            <h3 class="text-sm font-bold text-slate-900">{{ $label }}</h3>
                        </div>
                        @if (! in_array($kode, $hariAktif, true))
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-400">Bukan Hari Aktif</span>
                        @else
                            <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-bold text-indigo-700">{{ $daftar->count() }} JP</span>
                        @endif
                    </div>

                    @if ($daftar->isEmpty())
                        <div class="py-8 text-center">
                            <p class="text-xs text-slate-400">Belum ada jadwal pada hari ini.</p>
                        </div>
                    @else
                        <div class="mt-3 space-y-2">
                            @foreach ($daftar as $j)
                                <div class="flex items-center justify-between gap-2.5 rounded-xl border border-slate-200/80 bg-slate-50/50 p-2.5 shadow-2xs hover:bg-white transition"
                                     :class="terpilih.includes('{{ $j->id }}') ? 'border-indigo-400 bg-indigo-50/40' : ''">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <input type="checkbox" value="{{ $j->id }}" x-model="terpilih"
                                               class="cb-jadwal cb-hari-{{ $kode }} h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg text-xs font-black text-white shadow-2xs" style="background-color: {{ $j->subject->warna }}">
                                            {{ $j->jam_ke }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-bold text-slate-900">{{ $j->subject->nama }}</p>
                                            <p class="truncate text-[11px] text-slate-400 mt-0.5">
                                                {{ $j->jamRentang() ?? 'Jam belum ditentukan' }}
                                                @if ($j->guru_pengampu) &middot; {{ $j->guru_pengampu }} @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1 shrink-0">
                                        <a href="{{ route('admin.jadwal.edit', $j) }}"
                                           class="rounded-lg p-1 text-slate-500 hover:bg-slate-200/60 hover:text-slate-900 transition" title="Edit">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.jadwal.destroy', $j) }}"
                                              onsubmit="return confirm('Hapus JP {{ $j->jam_ke }} hari {{ $label }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="rounded-lg p-1 text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition" title="Hapus">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </x-card>

    <p class="text-xs text-slate-400">
        Mata pelajaran dapat berurutan di beberapa jam pelajaran (misal JP 1 s/d 3). Konfigurasi hari aktif sekolah dapat dikelola melalui
        @if (auth()->user()->isWaliKelas())
            <a href="{{ route('admin.pengaturan.edit') }}" class="font-bold text-indigo-600 hover:text-indigo-700 underline">Pengaturan Kelas</a>.
        @else
            menu Pengaturan Kelas oleh wali kelas.
        @endif
    </p>
</div>
@endsection
