@extends('layouts.admin')
@section('title', 'Dashboard')
@section('subtitle', \App\Support\Tanggal::panjang($hariIni))

@section('content')
<div class="space-y-6">
    {{-- Kartu Ringkasan Metrik Hari Ini --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <x-stat label="Hadir Hari Ini" :nilai="$ringkasan['hadir']" warna="#16a34a" :sub="'dari '.$ringkasan['siswa_aktif'].' siswa aktif'">
            <x-slot:ikon>
                <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot:ikon>
        </x-stat>

        <x-stat label="Sakit" :nilai="$ringkasan['sakit']" warna="#f59e0b" sub="Keterangan surat/laporan">
            <x-slot:ikon>
                <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </x-slot:ikon>
        </x-stat>

        <x-stat label="Izin" :nilai="$ringkasan['izin']" warna="#3b82f6" sub="Pemberitahuan ortu">
            <x-slot:ikon>
                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
            </x-slot:ikon>
        </x-stat>

        <x-stat label="Alpa (Tanpa Ket.)" :nilai="$ringkasan['alpa']" warna="#dc2626" sub="Perlu konfirmasi wali">
            <x-slot:ikon>
                <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot:ikon>
        </x-stat>

        <x-stat label="% Hari Ini" :nilai="$ringkasan['persen'].'%'" warna="#0f172a"
                :sub="$ringkasan['sudah_diinput'] ? 'Sudah terekam' : 'Belum selesai diinput'">
            <x-slot:ikon>
                <svg class="h-5 w-5 text-slate-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
            </x-slot:ikon>
        </x-stat>

        <x-stat label="% Bulan Ini" :nilai="$bulanIni['persen'].'%'" warna="#4f46e5"
                :sub="\App\Support\Tanggal::bulanTahun($hariIni)">
            <x-slot:ikon>
                <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
                </svg>
            </x-slot:ikon>
        </x-stat>
    </div>

    {{-- Status Input & Grafik Tren --}}
    <div class="grid gap-6 lg:grid-cols-3">
        <x-card judul="Status Input Hari Ini">
            <div class="rounded-xl border p-4 text-xs transition-all {{ $ringkasan['sudah_diinput'] ? 'border-emerald-200 bg-emerald-50/70 text-emerald-900' : 'border-amber-200 bg-amber-50/70 text-amber-900' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        @if ($ringkasan['sudah_diinput'])
                            <span class="grid h-5 w-5 place-items-center rounded-full bg-emerald-600 text-white">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            </span>
                        @else
                            <span class="grid h-5 w-5 place-items-center rounded-full bg-amber-500 text-white font-bold">!</span>
                        @endif
                        <span class="font-bold">Absensi Umum Harian</span>
                    </div>
                    <span class="font-semibold">{{ $ringkasan['sudah_diinput'] ? 'Sudah Diinput' : 'Belum Diinput' }}</span>
                </div>
                <div class="mt-2.5 pt-2 border-t border-slate-200/40 flex justify-end">
                    <a href="{{ route('admin.absensi-umum.index') }}" class="inline-flex items-center gap-1 font-bold text-indigo-700 hover:text-indigo-900 transition">
                        <span>{{ $ringkasan['sudah_diinput'] ? 'Periksa / Koreksi' : 'Input Sekarang' }}</span>
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </a>
                </div>
            </div>

            <div class="mt-5">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Jadwal Pelajaran ({{ $namaHari }})</p>
                    <span class="text-[11px] text-slate-400 font-medium">{{ $jadwalHariIni->count() }} JP</span>
                </div>

                @if (! $hariAktif)
                    <p class="rounded-xl bg-slate-50 p-3 text-xs text-slate-500 text-center border border-slate-200/60">
                        {{ $namaHari }} bukan merupakan hari aktif sekolah.
                    </p>
                @elseif ($jadwalHariIni->isEmpty())
                    <p class="rounded-xl bg-slate-50 p-3 text-xs text-slate-500 text-center border border-slate-200/60">
                        Belum ada jadwal mata pelajaran untuk hari ini.
                    </p>
                @else
                    @php
                        $sesiDashboard = collect();
                        $currentBlock = collect();
                        foreach ($jadwalHariIni as $j) {
                            if ($currentBlock->isEmpty()) {
                                $currentBlock->push($j);
                                continue;
                            }
                            $last = $currentBlock->last();
                            if ($last->subject_id === $j->subject_id && $j->jam_ke === $last->jam_ke + 1) {
                                $currentBlock->push($j);
                            } else {
                                $sesiDashboard->push($currentBlock);
                                $currentBlock = collect([$j]);
                            }
                        }
                        if ($currentBlock->isNotEmpty()) {
                            $sesiDashboard->push($currentBlock);
                        }
                    @endphp
                    <div class="space-y-1.5 max-h-56 overflow-y-auto pr-1">
                        @foreach ($sesiDashboard as $block)
                            @php
                                $first = $block->first();
                                $last = $block->last();
                                $count = $block->count();
                                $labelJp = $count > 1 ? 'JP ' . $first->jam_ke . '–' . $last->jam_ke : 'JP ' . $first->jam_ke;
                                $terisi = $block->every(fn($item) => $jpTerisi->has($item->id));
                            @endphp
                            <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-100 bg-slate-50/50 px-3 py-2 text-xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-md text-[10px] font-black text-white" style="background-color: {{ $first->subject->warna }}">
                                        {{ $first->subject->singkatan }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-slate-800">{{ $first->subject->nama }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $labelJp }} ({{ $count }} JP) @if($first->jamRentang()) &middot; {{ $first->jamRentang() }} @endif</p>
                                    </div>
                                </div>
                                @if ($terisi)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200/60 shrink-0">
                                        <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        Terisi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 border border-amber-200/60 shrink-0">
                                        Belum
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 text-right">
                        <a href="{{ route('admin.absensi-mapel.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-700 transition">
                            <span>Input Presensi Mapel</span>
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        </a>
                    </div>
                @endif
            </div>
        </x-card>

        <x-card class="lg:col-span-2" judul="Tren Kehadiran Harian (30 Hari Terakhir)">
            @if (empty($tren['labels']))
                <x-kosong pesan="Belum ada data absensi untuk digambar grafiknya." ikon="📉" />
            @else
                <div class="relative h-64 sm:h-72">
                    <canvas id="grafikTren"></canvas>
                </div>
            @endif
        </x-card>
    </div>

    {{-- Daftar Merah Alpa & Kehadiran Per Mapel --}}
    <div class="grid gap-6 lg:grid-cols-3">
        <x-card class="lg:col-span-2" padat>
            <x-slot:judul>
                <div class="flex items-center gap-2">
                    <span class="grid h-5 w-5 place-items-center rounded-md bg-rose-100 text-rose-700">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    </span>
                    <span>Peringatan Khusus: Alpa &ge; 3 Kali Bulan Ini</span>
                </div>
            </x-slot:judul>
            <x-slot:aksi>
                <a href="{{ route('admin.rekap', 'bulanan') }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-700 transition">
                    <span>Laporan Bulanan</span>
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            </x-slot:aksi>

            @if ($rawanAlpa->isEmpty())
                <div class="p-6">
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 text-center text-xs text-emerald-900">
                        <p class="font-bold">Kondisi Sangat Baik</p>
                        <p class="mt-0.5 text-emerald-700">Tidak ada siswa yang mencapai batas alpa 3 kali atau lebih pada bulan berjalan.</p>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-xs">
                        <thead class="bg-rose-50/60 text-left uppercase tracking-wider text-rose-900">
                            <tr>
                                <th class="px-5 py-3 font-bold">No. Absen</th>
                                <th class="px-5 py-3 font-bold">Nama Siswa</th>
                                <th class="px-5 py-3 font-bold">Akumulasi Alpa</th>
                                <th class="px-5 py-3 text-right font-bold">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($rawanAlpa as $r)
                                <tr class="hover:bg-rose-50/30 transition">
                                    <td class="px-5 py-3 tabular-nums font-bold text-slate-500">{{ $r->no_absen }}</td>
                                    <td class="px-5 py-3 font-bold text-slate-900">{{ $r->nama }}</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-800">
                                            {{ $r->alpa }}&times; Alpa
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('admin.rekap', ['jenis' => 'siswa', 'siswa' => $r->id]) }}" class="font-bold text-indigo-600 hover:text-indigo-700">
                                            Rekap Siswa &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>

        <x-card judul="Tingkat Kehadiran per Mapel (Bulan Ini)">
            @if ($perMapel->isEmpty())
                <x-kosong pesan="Belum ada data absensi mata pelajaran bulan ini." ikon="📚" />
            @else
                <div class="space-y-3.5 max-h-80 overflow-y-auto pr-1">
                    @foreach ($perMapel as $m)
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-slate-800 truncate">{{ $m->singkatan }}</span>
                                <span class="tabular-nums font-extrabold text-slate-700">{{ $m->persen }}%</span>
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

@push('scripts')
@if (! empty($tren['labels']))
<script>
new Chart(document.getElementById('grafikTren'), {
    type: 'line',
    data: {
        labels: @json($tren['labels']),
        datasets: [{
            label: '% Kehadiran',
            data: @json($tren['persen']),
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79, 70, 229, 0.08)',
            borderWidth: 2.5,
            pointRadius: 2,
            pointHoverRadius: 5,
            pointBackgroundColor: '#4f46e5',
            tension: 0.35,
            fill: true,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                min: 0,
                max: 100,
                ticks: {
                    callback: v => v + '%',
                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 }
                },
                grid: { color: 'rgba(226, 232, 240, 0.6)' }
            },
            x: {
                grid: { display: false },
                ticks: {
                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 },
                    maxRotation: 0,
                    autoSkip: true,
                    maxTicksLimit: 12
                }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0f172a',
                padding: 10,
                cornerRadius: 8,
                callbacks: { label: c => ` ${c.parsed.y}% hadir` }
            },
        },
    },
});
</script>
@endif
@endpush
@endsection
