@extends('layouts.admin')
@section('title', 'Mata Pelajaran')
@section('subtitle', 'Daftar mata pelajaran kelas &middot; Warna digunakan pada infografis grafik')

@section('content')
<div class="space-y-4">
    <x-card padat>
        <x-slot:judul>
            <div class="flex items-center gap-2">
                <span>Daftar Mata Pelajaran</span>
                <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-700">{{ $mapel->count() }} Mapel</span>
            </div>
        </x-slot:judul>
        <x-slot:aksi>
            <a href="{{ route('admin.mapel.create') }}"
               class="inline-flex items-center gap-1 rounded-xl bg-indigo-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Mapel</span>
            </a>
        </x-slot:aksi>

        @if ($mapel->isEmpty())
            <x-kosong pesan="Belum ada mata pelajaran yang didaftarkan. Tambahkan mata pelajaran sebelum membuat jadwal." ikon="📘" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50/80 text-left uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="w-16 px-4 py-3 font-bold text-center">Warna</th>
                            <th class="px-5 py-3 font-bold">Nama Mata Pelajaran</th>
                            <th class="px-4 py-3 font-bold">Singkatan</th>
                            <th class="px-4 py-3 font-bold">Jadwal Kelas</th>
                            <th class="px-4 py-3 text-center font-bold">Status</th>
                            <th class="px-5 py-3 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($mapel as $m)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block h-5 w-5 rounded-lg shadow-2xs ring-2 ring-white" style="background-color: {{ $m->warna }}"></span>
                                </td>
                                <td class="px-5 py-3 font-bold text-slate-900">{{ $m->nama }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-600">
                                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-700">{{ $m->singkatan }}</span>
                                </td>
                                <td class="px-4 py-3 tabular-nums font-semibold text-slate-600">{{ $m->schedules_count }} JP Terjadwal</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($m->is_active)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 border border-emerald-200/60">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-500 border border-slate-200">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('admin.mapel.edit', $m) }}"
                                           class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 transition">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.mapel.destroy', $m) }}"
                                              onsubmit="return confirm('Hapus mata pelajaran ini?')">
                                            @csrf @method('DELETE')
                                            <button class="rounded-lg border border-rose-200 bg-white px-2.5 py-1 text-xs font-bold text-rose-700 shadow-2xs hover:bg-rose-50 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</div>
@endsection
