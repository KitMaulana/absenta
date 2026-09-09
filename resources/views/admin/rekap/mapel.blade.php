@extends('layouts.admin')
@section('title', $judul)
@section('subtitle', $periode->label())

@section('content')
@include('admin.rekap._filter', ['ekstra' => view('admin.rekap._pilih-mapel', ['daftarMapel' => $daftarMapel, 'mapel' => $mapel])])

@if (! $mapel)
    <x-card><x-kosong pesan="Belum ada mata pelajaran aktif." ikon="📘" /></x-card>
@else
<x-card padat>
    <x-slot:judul>
        <div class="flex items-center gap-2">
            <span class="inline-block h-3.5 w-3.5 rounded-md shadow-2xs" style="background-color: {{ $mapel->warna }}"></span>
            <span>Rekapitulasi: {{ $mapel->nama }}</span>
        </div>
    </x-slot:judul>

    @if ($baris->isEmpty())
        <x-kosong pesan="Belum ada data absensi untuk mata pelajaran ini pada periode terpilih." ikon="📚" />
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-xs">
                <thead class="bg-slate-50/80 text-left uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="w-12 px-4 py-3 font-bold text-center">No</th>
                        <th class="px-4 py-3 font-bold">Nama Lengkap</th>
                        <th class="px-3 py-3 text-center font-bold text-emerald-800 bg-emerald-50/40">Hadir</th>
                        <th class="px-3 py-3 text-center font-bold text-amber-800 bg-amber-50/40">Sakit</th>
                        <th class="px-3 py-3 text-center font-bold text-blue-800 bg-blue-50/40">Izin</th>
                        <th class="px-3 py-3 text-center font-bold text-rose-800 bg-rose-50/40">Alpa</th>
                        <th class="px-3 py-3 text-center font-bold text-violet-800 bg-violet-50/40">Disp.</th>
                        <th class="px-3 py-3 text-center font-bold">Total JP</th>
                        <th class="px-4 py-3 text-right font-bold bg-slate-100/60">% Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($baris as $b)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-2.5 text-center tabular-nums font-bold text-slate-400">{{ $b->siswa->no_absen }}</td>
                            <td class="px-4 py-2.5 font-bold text-slate-900">
                                <div class="flex items-center gap-2">
                                    <div class="grid h-6 w-6 place-items-center rounded-md bg-slate-100 text-[10px] font-bold text-slate-700">
                                        {{ $b->siswa->no_absen }}
                                    </div>
                                    <span>{{ $b->siswa->nama }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-2.5 text-center tabular-nums font-bold text-emerald-700 bg-emerald-50/20">{{ $b->hitung['hadir'] }}</td>
                            <td class="px-3 py-2.5 text-center tabular-nums text-amber-700 bg-amber-50/20">{{ $b->hitung['sakit'] }}</td>
                            <td class="px-3 py-2.5 text-center tabular-nums text-blue-700 bg-blue-50/20">{{ $b->hitung['izin'] }}</td>
                            <td class="px-3 py-2.5 text-center tabular-nums font-bold text-rose-700 bg-rose-50/20">{{ $b->hitung['alpa'] }}</td>
                            <td class="px-3 py-2.5 text-center tabular-nums text-violet-700 bg-violet-50/20">{{ $b->hitung['dispensasi'] }}</td>
                            <td class="px-3 py-2.5 text-center tabular-nums font-medium text-slate-500">{{ $b->total }}</td>
                            <td class="px-4 py-2.5 text-right bg-slate-50/40">
                                <div class="flex items-center justify-end gap-2.5">
                                    <div class="h-2 w-20 overflow-hidden rounded-full bg-slate-200/70">
                                        <div class="h-full rounded-full transition-all" style="width: {{ $b->persen }}%; background-color: {{ $mapel->warna }}"></div>
                                    </div>
                                    <span class="w-12 tabular-nums font-extrabold {{ $b->persen < 75 ? 'text-rose-600' : 'text-slate-900' }}">{{ $b->persen }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-card>
@endif
@endsection
