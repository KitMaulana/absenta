@extends('layouts.admin')
@section('title', 'Jadwal Pelajaran')
@section('subtitle', 'Jadwal jam pelajaran (JP) kelas per hari sebagai acuan presensi per mapel')

@section('content')
<div class="space-y-4">
    <x-card padat>
        <x-slot:judul>
            <div class="flex items-center gap-2">
                <span>Jadwal Pelajaran Mingguan</span>
            </div>
        </x-slot:judul>
        <x-slot:aksi>
            <a href="{{ route('admin.jadwal.create') }}"
               class="inline-flex items-center gap-1 rounded-xl bg-indigo-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Jam Pelajaran</span>
            </a>
        </x-slot:aksi>

        <div class="grid gap-px bg-slate-200/80 sm:grid-cols-2 xl:grid-cols-3">
            @foreach (\App\Models\Schedule::HARI as $kode => $label)
                @php $daftar = $perHari->get($kode, collect()); @endphp
                <div class="bg-white p-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold text-slate-900">{{ $label }}</h3>
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
                                <div class="flex items-center justify-between gap-2.5 rounded-xl border border-slate-200/80 bg-slate-50/50 p-2.5 shadow-2xs hover:bg-white transition">
                                    <div class="flex items-center gap-2.5 min-w-0">
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
        Mata pelajaran dapat berurutan di beberapa jam pelajaran (misal JP 1 &amp; 2). Konfigurasi hari aktif sekolah dapat dikelola melalui
        @if (auth()->user()->isWaliKelas())
            <a href="{{ route('admin.pengaturan.edit') }}" class="font-bold text-indigo-600 hover:text-indigo-700 underline">Pengaturan Kelas</a>.
        @else
            menu Pengaturan Kelas oleh wali kelas.
        @endif
    </p>
</div>
@endsection
