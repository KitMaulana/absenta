@extends('layouts.admin')
@section('title', $judul)
@section('subtitle', $periode->label())

@section('content')
@include('admin.rekap._filter')

{{-- Ringkasan Metrik Periode Semester --}}
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6 mb-5">
    <x-stat label="Hari Efektif" :nilai="$jumlahHariEfektif" warna="#0f172a" sub="Kalender aktif semester">
        <x-slot:ikon>
            <svg class="h-5 w-5 text-slate-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
        </x-slot:ikon>
    </x-stat>

    @foreach (\App\Enums\AttendanceStatus::cases() as $status)
        <x-stat :label="$status->label()" :nilai="$ringkasan[$status->value]" :warna="$status->warna()" sub="Akumulasi semester" />
    @endforeach
</div>

<div class="grid gap-6 lg:grid-cols-3">
    {{-- Tabel Rekapitulasi per Siswa Selama Semester --}}
    <x-card class="lg:col-span-2" padat>
        <x-slot:judul>
            <div class="flex items-center gap-2">
                <span>Rekapitulasi Kehadiran Semester per Siswa</span>
                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">{{ $baris->count() }} Siswa</span>
            </div>
        </x-slot:judul>

        @if ($baris->isEmpty())
            <x-kosong pesan="Belum ada data siswa aktif." ikon="🧑‍🎓" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50/80 text-left uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="w-12 px-4 py-3 font-bold text-center">No</th>
                            <th class="px-4 py-3 font-bold">Nama Lengkap</th>
                            <th class="px-3 py-3 text-center font-bold text-emerald-800 bg-emerald-50/40">H</th>
                            <th class="px-3 py-3 text-center font-bold text-amber-800 bg-amber-50/40">S</th>
                            <th class="px-3 py-3 text-center font-bold text-blue-800 bg-blue-50/40">I</th>
                            <th class="px-3 py-3 text-center font-bold text-rose-800 bg-rose-50/40">A</th>
                            <th class="px-3 py-3 text-center font-bold text-violet-800 bg-violet-50/40">D</th>
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
                                <td class="px-4 py-2.5 text-right tabular-nums font-extrabold bg-slate-50/40 {{ $b->persen < 75 ? 'text-rose-600' : 'text-slate-900' }}">{{ $b->persen }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    <div class="space-y-5">
        {{-- Kartu Peringatan Kehadiran < 75% (Umum & Mapel) --}}
        <x-card judul="Peringatan Kehadiran &lt; 75% (Umum &amp; Mapel)">
            @if ($peringatanSemester['gabungan']->isEmpty())
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 text-center text-xs text-emerald-900">
                    <p class="font-bold">Semua Siswa &ge; 75%</p>
                    <p class="mt-0.5 text-emerald-700">Seluruh siswa memenuhi batas minimal 75% baik harian umum maupun mata pelajaran.</p>
                </div>
            @else
                <div class="space-y-2.5 max-h-80 overflow-y-auto pr-1">
                    @foreach ($peringatanSemester['gabungan'] as $item)
                        <div class="rounded-xl border border-rose-200/70 bg-rose-50/60 p-3 text-xs">
                            <div class="flex items-center justify-between gap-2">
                                <a href="{{ route('admin.rekap', ['jenis' => 'siswa', 'siswa' => $item->siswa->id, 'periode' => 'semester']) }}"
                                   class="truncate font-bold text-rose-950 hover:text-indigo-700">
                                    {{ $item->siswa->no_absen }}. {{ $item->siswa->nama }}
                                </a>
                                @if ($item->is_umum_rendah)
                                    <span class="shrink-0 rounded-md bg-rose-200/80 px-2 py-0.5 text-[10px] font-black text-rose-900">
                                        Umum: {{ $item->umum->persen }}%
                                    </span>
                                @endif
                            </div>
                            @if ($item->mapel_rendah->isNotEmpty())
                                <div class="mt-1.5 flex flex-wrap gap-1">
                                    @foreach ($item->mapel_rendah as $m)
                                        <span class="inline-flex items-center gap-1 rounded bg-white/90 border border-rose-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-800">
                                            <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $m->warna }}"></span>
                                            {{ $m->singkatan }}: <strong class="text-rose-700">{{ $m->persen }}%</strong>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        {{-- Kartu Perhatian Alpa --}}
        <x-card judul="Perhatian Khusus (Alpa &ge; 3)">
            @if ($rawanAlpa->isEmpty())
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 text-center text-xs text-emerald-900">
                    <p class="font-bold">Kehadiran Baik</p>
                    <p class="mt-0.5 text-emerald-700">Tidak ada siswa yang mencapai batas alpa 3 kali pada semester ini.</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($rawanAlpa as $r)
                        <div class="flex items-center justify-between gap-2 rounded-xl bg-rose-50/80 border border-rose-200/60 p-2.5 text-xs">
                            <span class="truncate font-bold text-rose-950">{{ $r->no_absen }}. {{ $r->nama }}</span>
                            <span class="shrink-0 rounded-md bg-rose-100 px-2 py-0.5 text-[11px] font-black text-rose-800">{{ $r->alpa }}&times; Alpa</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        {{-- Kehadiran per Mapel dalam Semester --}}
        <x-card judul="Rerata Kehadiran per Mapel (Semester)">
            @if ($perMapel->isEmpty())
                <x-kosong pesan="Belum ada data mata pelajaran." ikon="📚" />
            @else
                <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                    @foreach ($perMapel as $m)
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-slate-800 truncate">{{ $m->singkatan }}</span>
                                <span class="tabular-nums font-bold text-slate-700">{{ $m->persen }}%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full transition-all duration-500" style="width: {{ $m->persen }}%; background-color: {{ $m->warna }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>
</div>
@endsection
