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
            @if ($liburHariIni)
                <div class="rounded-xl border border-amber-300 bg-amber-100/70 p-4 text-xs text-amber-950 transition-all">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-base">🏖️</span>
                            <span class="font-bold">Hari Libur</span>
                        </div>
                        <span class="font-bold text-amber-900 truncate max-w-[10rem]" title="{{ $liburHariIni->keterangan }}">{{ $liburHariIni->keterangan }}</span>
                    </div>
                    <p class="mt-2 text-[11px] text-amber-800">
                        Presensi dinonaktifkan untuk seluruh siswa & mapel.
                    </p>
                    <div class="mt-2.5 pt-2 border-t border-amber-200 flex justify-end">
                        <a href="{{ route('admin.absensi-umum.index') }}" class="inline-flex items-center gap-1 font-bold text-amber-900 hover:text-amber-950 transition">
                            <span>Buka Absensi Umum</span>
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        </a>
                    </div>
                </div>
            @else
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
            @endif

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

    {{-- Peringatan Kehadiran 1 Semester < 75% (Real-time: Harian Umum & Mata Pelajaran) --}}
    @php
        $statSem = $peringatanSemester['statistik'];
        $adaPeringatanSemester = $statSem['total_siswa_berisiko'] > 0;
    @endphp
    <div x-data="{
        tab: 'gabungan',
        risiko: 'semua',
        mapelId: 'semua',
        cari: '',
        cocokNama(nama, noAbsen) {
            if (!this.cari.trim()) return true;
            const q = this.cari.toLowerCase().trim();
            return nama.toLowerCase().includes(q) || String(noAbsen).includes(q);
        },
        cocokRisiko(kat) {
            return this.risiko === 'semua' || this.risiko === kat;
        },
        cocokMapel(id) {
            return this.mapelId === 'semua' || String(this.mapelId) === String(id);
        }
    }">
        <x-card padat class="{{ $adaPeringatanSemester ? 'border-rose-200/90 ring-1 ring-rose-500/10' : '' }}">
            <x-slot:judul>
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="grid h-6 w-6 place-items-center rounded-lg {{ $adaPeringatanSemester ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-extrabold text-slate-900">Peringatan Kehadiran 1 Semester &lt; 75%</span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-2.5 py-0.5 text-[10px] font-bold text-indigo-700 border border-indigo-200/70">
                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-600 animate-pulse"></span>
                                Real-time &middot; {{ $periodeSemester->namaSemester() }}
                            </span>
                        </div>
                        <p class="text-[11px] font-normal text-slate-500 mt-0.5">
                            Periode {{ \App\Support\Tanggal::pendek($periodeSemester->mulai) }} s.d. {{ \App\Support\Tanggal::pendek($periodeSemester->selesai) }}
                            ({{ $peringatanSemester['hari_efektif'] }} hari efektif berjalan) &middot; Ambang batas minimal kehadiran <strong>75%</strong>
                        </p>
                    </div>
                </div>
            </x-slot:judul>
            <x-slot:aksi>
                <a href="{{ route('admin.rekap', ['jenis' => 'semester', 'periode' => 'semester']) }}"
                   class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition">
                    <span>Laporan Semester</span>
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
                <a href="{{ route('admin.rekap.pdf', ['jenis' => 'semester', 'periode' => 'semester']) }}" target="_blank"
                   class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-2.5 py-1.5 text-xs font-bold text-white shadow-2xs hover:bg-indigo-700 transition">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
                    <span>PDF Semester</span>
                </a>
            </x-slot:aksi>

            {{-- Ringkasan Metrik Peringatan Semester --}}
            <div class="grid gap-3 border-b border-slate-100 bg-slate-50/50 p-4 sm:px-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border {{ $statSem['total_siswa_berisiko'] > 0 ? 'border-rose-200 bg-rose-50/60' : 'border-emerald-200 bg-emerald-50/60' }} p-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider {{ $statSem['total_siswa_berisiko'] > 0 ? 'text-rose-700' : 'text-emerald-700' }}">Total Siswa &lt; 75%</span>
                        <span class="rounded-md {{ $statSem['total_siswa_berisiko'] > 0 ? 'bg-rose-200/80 text-rose-900' : 'bg-emerald-200/80 text-emerald-900' }} px-1.5 py-0.5 text-[10px] font-extrabold">
                            {{ $statSem['total_siswa_berisiko'] > 0 ? 'Perlu Tindakan' : 'Aman' }}
                        </span>
                    </div>
                    <p class="mt-1 text-2xl font-black tabular-nums {{ $statSem['total_siswa_berisiko'] > 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                        {{ $statSem['total_siswa_berisiko'] }} <span class="text-xs font-bold">Siswa</span>
                    </p>
                    <p class="mt-0.5 text-[11px] {{ $statSem['total_siswa_berisiko'] > 0 ? 'text-rose-800/80' : 'text-emerald-800/80' }}">
                        @if ($statSem['jumlah_keduanya'] > 0)
                            {{ $statSem['jumlah_keduanya'] }} siswa di bawah 75% pada umum &amp; mapel
                        @else
                            Gabungan absensi harian umum &amp; mapel
                        @endif
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200/80 bg-white p-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Harian Umum &lt; 75%</span>
                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">Semester: {{ $semesterIni['persen'] }}%</span>
                    </div>
                    <p class="mt-1 text-2xl font-black tabular-nums {{ $statSem['jumlah_umum_rendah'] > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                        {{ $statSem['jumlah_umum_rendah'] }} <span class="text-xs font-bold text-slate-500">Siswa</span>
                    </p>
                    <p class="mt-0.5 text-[11px] text-slate-500">
                        Berdasarkan presensi harian umum semester
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200/80 bg-white p-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Absensi Mapel &lt; 75%</span>
                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-600">{{ $statSem['jumlah_kasus_mapel'] }} Kasus Mapel</span>
                    </div>
                    <p class="mt-1 text-2xl font-black tabular-nums {{ $statSem['jumlah_mapel_rendah'] > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                        {{ $statSem['jumlah_mapel_rendah'] }} <span class="text-xs font-bold text-slate-500">Siswa</span>
                    </p>
                    <p class="mt-0.5 text-[11px] text-slate-500">
                        Tersebar pada {{ $peringatanSemester['per_mapel_grup']->count() }} mata pelajaran
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200/80 bg-white p-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Sebaran Tingkat Risiko</span>
                    <div class="mt-2 grid grid-cols-3 gap-1.5 text-center">
                        <button type="button" @click="risiko = (risiko === 'kritis' ? 'semua' : 'kritis')"
                                :class="risiko === 'kritis' ? 'ring-2 ring-rose-600 bg-rose-100' : 'bg-rose-50/80 hover:bg-rose-100/70'"
                                class="rounded-lg border border-rose-200/80 px-1.5 py-1 transition">
                            <span class="block text-[9px] font-extrabold uppercase text-rose-700">&lt; 50% Kritis</span>
                            <span class="text-sm font-black tabular-nums text-rose-800">{{ $statSem['kritis'] }}</span>
                        </button>
                        <button type="button" @click="risiko = (risiko === 'rawan' ? 'semua' : 'rawan')"
                                :class="risiko === 'rawan' ? 'ring-2 ring-orange-500 bg-orange-100' : 'bg-orange-50/80 hover:bg-orange-100/70'"
                                class="rounded-lg border border-orange-200/80 px-1.5 py-1 transition">
                            <span class="block text-[9px] font-extrabold uppercase text-orange-700">50–64% Rawan</span>
                            <span class="text-sm font-black tabular-nums text-orange-800">{{ $statSem['rawan'] }}</span>
                        </button>
                        <button type="button" @click="risiko = (risiko === 'waspada' ? 'semua' : 'waspada')"
                                :class="risiko === 'waspada' ? 'ring-2 ring-amber-500 bg-amber-100' : 'bg-amber-50/80 hover:bg-amber-100/70'"
                                class="rounded-lg border border-amber-200/80 px-1.5 py-1 transition">
                            <span class="block text-[9px] font-extrabold uppercase text-amber-700">65–74% Waspada</span>
                            <span class="text-sm font-black tabular-nums text-amber-800">{{ $statSem['waspada'] }}</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Navigasi Tab & Pencarian --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-white px-4 py-3 sm:px-6">
                <div class="flex flex-wrap items-center gap-1.5" role="tablist">
                    <button type="button" @click="tab = 'gabungan'"
                            :class="tab === 'gabungan' ? 'bg-slate-900 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        <span>Semua Siswa Berisiko</span>
                        <span :class="tab === 'gabungan' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                              class="rounded-full px-1.5 py-0.2 text-[10px] font-extrabold tabular-nums">{{ $statSem['total_siswa_berisiko'] }}</span>
                    </button>

                    <button type="button" @click="tab = 'umum'"
                            :class="tab === 'umum' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        <span>Absensi Harian Umum (&lt; 75%)</span>
                        <span :class="tab === 'umum' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-700'"
                              class="rounded-full px-1.5 py-0.2 text-[10px] font-extrabold tabular-nums">{{ $statSem['jumlah_umum_rendah'] }}</span>
                    </button>

                    <button type="button" @click="tab = 'mapel'"
                            :class="tab === 'mapel' ? 'bg-indigo-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/70'"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition">
                        <span>Absensi per Mapel (&lt; 75%)</span>
                        <span :class="tab === 'mapel' ? 'bg-white/20 text-white' : 'bg-indigo-100 text-indigo-700'"
                              class="rounded-full px-1.5 py-0.2 text-[10px] font-extrabold tabular-nums">{{ $statSem['jumlah_kasus_mapel'] }}</span>
                    </button>
                </div>

                @if ($adaPeringatanSemester)
                    <div class="flex flex-wrap items-center gap-2">
                        <template x-if="risiko !== 'semua'">
                            <button type="button" @click="risiko = 'semua'"
                                    class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-200">
                                <span>Filter: <span class="uppercase" x-text="risiko"></span></span>
                                <span>&times;</span>
                            </button>
                        </template>
                        <div class="relative">
                            <input type="search" x-model="cari" placeholder="Cari nama / no. absen..."
                                   class="w-48 sm:w-56 rounded-xl border-slate-200 bg-slate-50 py-1.5 pl-8 pr-3 text-xs text-slate-800 placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500">
                            <svg class="pointer-events-none absolute left-2.5 top-2 h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                        </div>
                    </div>
                @endif
            </div>

            {{-- TAB 1: RINGKASAN TERPADU (GABUNGAN UMUM & MAPEL) --}}
            <div x-show="tab === 'gabungan'">
                @if ($peringatanSemester['gabungan']->isEmpty())
                    <div class="p-6">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 text-center text-xs text-emerald-900">
                            <p class="font-bold">Kondisi Kehadiran Semester Sangat Baik</p>
                            <p class="mt-0.5 text-emerald-700">Seluruh siswa aktif memiliki tingkat kehadiran &ge; 75% baik pada Absensi Harian Umum maupun seluruh Mata Pelajaran selama semester berjalan.</p>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-xs">
                            <thead class="bg-slate-50/90 text-left uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="px-5 py-3 font-bold">Siswa &amp; Tingkat Risiko</th>
                                    <th class="px-5 py-3 font-bold">Absensi Harian Umum (Semester)</th>
                                    <th class="px-5 py-3 font-bold">Absensi Mata Pelajaran (&lt; 75%)</th>
                                    <th class="px-5 py-3 text-right font-bold">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($peringatanSemester['gabungan'] as $item)
                                    @php
                                        $badgeKat = match ($item->kategori) {
                                            'kritis' => ['label' => 'Kritis (<50%)', 'cls' => 'bg-rose-100 text-rose-800 border-rose-200'],
                                            'rawan' => ['label' => 'Rawan (50–64%)', 'cls' => 'bg-orange-100 text-orange-800 border-orange-200'],
                                            default => ['label' => 'Waspada (65–74%)', 'cls' => 'bg-amber-100 text-amber-800 border-amber-200'],
                                        };
                                    @endphp
                                    <tr x-show="cocokNama(@js($item->siswa->nama), @js($item->siswa->no_absen)) && cocokRisiko(@js($item->kategori))"
                                        class="hover:bg-rose-50/20 transition align-top">
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-start gap-2.5">
                                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-slate-100 text-xs font-extrabold tabular-nums text-slate-700">
                                                    {{ $item->siswa->no_absen }}
                                                </span>
                                                <div>
                                                    <p class="font-bold text-slate-900">{{ $item->siswa->nama }}</p>
                                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                        <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[10px] font-extrabold {{ $badgeKat['cls'] }}">
                                                            {{ $badgeKat['label'] }}
                                                        </span>
                                                        @if ($item->is_umum_rendah && $item->is_mapel_rendah)
                                                            <span class="inline-flex items-center rounded-md bg-rose-600 px-1.5 py-0.5 text-[10px] font-bold text-white">
                                                                Umum &amp; Mapel
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-5 py-3.5">
                                            @if ($item->umum->total === 0)
                                                <span class="text-slate-400 italic">Belum ada data harian</span>
                                            @elseif ($item->is_umum_rendah)
                                                <div class="max-w-xs space-y-1.5">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-0.5 text-xs font-black tabular-nums text-rose-800">
                                                            {{ $item->umum->persen }}%
                                                        </span>
                                                        <span class="text-[11px] font-medium text-slate-500">
                                                            {{ $item->umum->hadir_efektif }}/{{ $item->umum->total }} hari hadir
                                                        </span>
                                                    </div>
                                                    <div class="relative h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                                        <div class="h-full rounded-full {{ $item->umum->persen < 50 ? 'bg-rose-600' : ($item->umum->persen < 65 ? 'bg-orange-500' : 'bg-amber-500') }}"
                                                             style="width: {{ min(100, $item->umum->persen) }}%"></div>
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-1 text-[10px] font-bold">
                                                        <span class="rounded bg-emerald-50 px-1.5 py-0.5 text-emerald-700">H:{{ $item->umum->hitung['hadir'] }}</span>
                                                        <span class="rounded bg-amber-50 px-1.5 py-0.5 text-amber-700">S:{{ $item->umum->hitung['sakit'] }}</span>
                                                        <span class="rounded bg-blue-50 px-1.5 py-0.5 text-blue-700">I:{{ $item->umum->hitung['izin'] }}</span>
                                                        <span class="rounded bg-rose-100 px-1.5 py-0.5 text-rose-800">A:{{ $item->umum->hitung['alpa'] }}</span>
                                                        @if ($item->umum->hitung['dispensasi'] > 0)
                                                            <span class="rounded bg-violet-50 px-1.5 py-0.5 text-violet-700">D:{{ $item->umum->hitung['dispensasi'] }}</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-[10px] font-semibold text-rose-700">
                                                        Target 75%: perlu <strong>+{{ $item->umum->butuh_hadir }} hari</strong> hadir berturut-turut
                                                    </p>
                                                </div>
                                            @else
                                                <div class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50/70 px-2.5 py-1 text-emerald-800">
                                                    <svg class="h-3.5 w-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                    <span class="font-extrabold tabular-nums">{{ $item->umum->persen }}%</span>
                                                    <span class="text-[10px] text-emerald-700">({{ $item->umum->hadir_efektif }}/{{ $item->umum->total }} hari)</span>
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-5 py-3.5">
                                            @if ($item->mapel_rendah->isNotEmpty())
                                                <div class="flex flex-wrap gap-1.5 max-w-md">
                                                    @foreach ($item->mapel_rendah as $m)
                                                        <a href="{{ route('admin.rekap', ['jenis' => 'mapel', 'mapel' => $m->subject_id, 'periode' => 'semester']) }}"
                                                           title="{{ $m->nama }}: Hadir {{ $m->hadir_efektif }}/{{ $m->total }} JP (S:{{ $m->sakit }}, I:{{ $m->izin }}, A:{{ $m->alpa }}) • Butuh +{{ $m->butuh_jp }} JP untuk mencapai 75%"
                                                           class="group inline-flex items-center gap-1.5 rounded-lg border border-rose-200/90 bg-rose-50/60 px-2 py-1 text-[11px] hover:border-rose-400 hover:bg-rose-100/60 transition">
                                                            <span class="h-2 w-2 rounded-full shrink-0" style="background-color: {{ $m->warna }}"></span>
                                                            <span class="font-bold text-slate-800">{{ $m->singkatan }}</span>
                                                            <span class="font-black tabular-nums text-rose-700">{{ $m->persen }}%</span>
                                                            <span class="text-[10px] text-slate-500">({{ $m->hadir_efektif }}/{{ $m->total }} JP)</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                                <p class="mt-1.5 text-[10px] text-slate-500">
                                                    Total seluruh mapel: <strong class="{{ $item->ringkasan_mapel->dibawah_ambang ? 'text-rose-600' : 'text-slate-700' }}">{{ $item->ringkasan_mapel->persen }}%</strong>
                                                    ({{ $item->ringkasan_mapel->hadir_efektif }}/{{ $item->ringkasan_mapel->total }} JP)
                                                </p>
                                            @elseif ($item->ringkasan_mapel->dibawah_ambang)
                                                <span class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-800">
                                                    Total JP: {{ $item->ringkasan_mapel->persen }}% ({{ $item->ringkasan_mapel->hadir_efektif }}/{{ $item->ringkasan_mapel->total }} JP)
                                                </span>
                                            @elseif ($item->ringkasan_mapel->total === 0)
                                                <span class="text-slate-400 italic">Belum ada data JP mapel</span>
                                            @else
                                                <div class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50/70 px-2.5 py-1 text-emerald-800">
                                                    <svg class="h-3.5 w-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                    <span class="font-bold">Semua Mapel &ge; 75%</span>
                                                    <span class="text-[10px] text-emerald-700">(Rerata {{ $item->ringkasan_mapel->persen }}%)</span>
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-5 py-3.5 text-right">
                                            <div class="inline-flex flex-col items-end gap-1.5">
                                                <a href="{{ route('admin.rekap', ['jenis' => 'siswa', 'siswa' => $item->siswa->id, 'periode' => 'semester']) }}"
                                                   class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 px-2.5 py-1 text-[11px] font-bold text-indigo-700 hover:bg-indigo-100 transition">
                                                    <span>Detail Siswa</span>
                                                    <span>&rarr;</span>
                                                </a>
                                                @if ($item->wa_url)
                                                    <a href="{{ $item->wa_url }}" target="_blank" rel="noopener"
                                                       class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-emerald-700 transition">
                                                        <span>WA Ortu</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- TAB 2: ABSENSI HARIAN UMUM < 75% --}}
            <div x-show="tab === 'umum'" x-cloak>
                @if ($peringatanSemester['umum']->isEmpty())
                    <div class="p-6">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 text-center text-xs text-emerald-900">
                            <p class="font-bold">Absensi Harian Umum Aman (&ge; 75%)</p>
                            <p class="mt-0.5 text-emerald-700">Tidak ada siswa yang persentase kehadiran harian umumnya di bawah 75% pada semester berjalan.</p>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-xs">
                            <thead class="bg-rose-50/60 text-left uppercase tracking-wider text-rose-900">
                                <tr>
                                    <th class="px-5 py-3 font-bold">No</th>
                                    <th class="px-5 py-3 font-bold">Nama Siswa</th>
                                    <th class="px-3 py-3 text-center font-bold">H</th>
                                    <th class="px-3 py-3 text-center font-bold">S</th>
                                    <th class="px-3 py-3 text-center font-bold">I</th>
                                    <th class="px-3 py-3 text-center font-bold">A</th>
                                    <th class="px-3 py-3 text-center font-bold">D</th>
                                    <th class="px-3 py-3 text-center font-bold">Total Hari</th>
                                    <th class="px-5 py-3 font-bold">% Kehadiran Umum</th>
                                    <th class="px-5 py-3 font-bold">Simulasi Pemulihan (&ge; 75%)</th>
                                    <th class="px-5 py-3 text-right font-bold">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($peringatanSemester['umum'] as $item)
                                    <tr x-show="cocokNama(@js($item->siswa->nama), @js($item->siswa->no_absen)) && cocokRisiko(@js($item->umum->kategori))"
                                        class="hover:bg-rose-50/30 transition">
                                        <td class="px-5 py-3 tabular-nums font-bold text-slate-500">{{ $item->siswa->no_absen }}</td>
                                        <td class="px-5 py-3 font-bold text-slate-900">{{ $item->siswa->nama }}</td>
                                        <td class="px-3 py-3 text-center tabular-nums font-bold text-emerald-700">{{ $item->umum->hitung['hadir'] }}</td>
                                        <td class="px-3 py-3 text-center tabular-nums text-amber-700">{{ $item->umum->hitung['sakit'] }}</td>
                                        <td class="px-3 py-3 text-center tabular-nums text-blue-700">{{ $item->umum->hitung['izin'] }}</td>
                                        <td class="px-3 py-3 text-center tabular-nums font-extrabold text-rose-700">{{ $item->umum->hitung['alpa'] }}</td>
                                        <td class="px-3 py-3 text-center tabular-nums text-violet-700">{{ $item->umum->hitung['dispensasi'] }}</td>
                                        <td class="px-3 py-3 text-center tabular-nums font-semibold text-slate-600">{{ $item->umum->total }}</td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-black tabular-nums text-rose-800">
                                                    {{ $item->umum->persen }}%
                                                </span>
                                                <span class="text-[10px] font-semibold text-rose-600">(-{{ $item->umum->selisih_persen }}%)</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-bold text-amber-900">
                                                +{{ $item->umum->butuh_hadir }} hari hadir berturut-turut
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('admin.rekap', ['jenis' => 'siswa', 'siswa' => $item->siswa->id, 'periode' => 'semester']) }}"
                                                   class="font-bold text-indigo-600 hover:text-indigo-700">
                                                    Rekap Siswa &rarr;
                                                </a>
                                                @if ($item->wa_url)
                                                    <a href="{{ $item->wa_url }}" target="_blank" rel="noopener"
                                                       class="rounded-lg bg-emerald-600 px-2 py-1 text-[10px] font-bold text-white hover:bg-emerald-700">
                                                        WA Ortu
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- TAB 3: ABSENSI MATA PELAJARAN < 75% --}}
            <div x-show="tab === 'mapel'" x-cloak>
                @if ($peringatanSemester['mapel_items']->isEmpty())
                    <div class="p-6">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 text-center text-xs text-emerald-900">
                            <p class="font-bold">Absensi Seluruh Mata Pelajaran Aman (&ge; 75%)</p>
                            <p class="mt-0.5 text-emerald-700">Tidak ada siswa yang kehadirannya di bawah 75% pada mata pelajaran manapun selama semester berjalan.</p>
                        </div>
                    </div>
                @else
                    {{-- Filter Pills per Mata Pelajaran --}}
                    <div class="flex flex-wrap items-center gap-1.5 border-b border-slate-100 bg-slate-50/60 px-4 py-2.5 sm:px-6">
                        <span class="text-[11px] font-bold text-slate-500 mr-1">Filter Mapel:</span>
                        <button type="button" @click="mapelId = 'semua'"
                                :class="mapelId === 'semua' ? 'bg-slate-800 text-white' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'"
                                class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 text-[11px] font-bold transition">
                            <span>Semua Mapel</span>
                            <span class="opacity-80">({{ $peringatanSemester['mapel_items']->count() }})</span>
                        </button>
                        @foreach ($peringatanSemester['per_mapel_grup'] as $g)
                            <button type="button" @click="mapelId = '{{ $g->subject_id }}'"
                                    :class="String(mapelId) === '{{ $g->subject_id }}' ? 'ring-2 ring-indigo-600 bg-indigo-50 text-indigo-900 border-indigo-300' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'"
                                    class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-[11px] font-bold transition">
                                <span class="h-2 w-2 rounded-full" style="background-color: {{ $g->warna }}"></span>
                                <span>{{ $g->singkatan }}</span>
                                <span class="rounded bg-rose-100 px-1.5 py-0.2 text-[10px] font-extrabold text-rose-800">{{ $g->jumlah_siswa }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-xs">
                            <thead class="bg-indigo-50/50 text-left uppercase tracking-wider text-indigo-950">
                                <tr>
                                    <th class="px-5 py-3 font-bold">No</th>
                                    <th class="px-5 py-3 font-bold">Nama Siswa</th>
                                    <th class="px-5 py-3 font-bold">Mata Pelajaran</th>
                                    <th class="px-3 py-3 text-center font-bold">H</th>
                                    <th class="px-3 py-3 text-center font-bold">S</th>
                                    <th class="px-3 py-3 text-center font-bold">I</th>
                                    <th class="px-3 py-3 text-center font-bold">A</th>
                                    <th class="px-3 py-3 text-center font-bold">D</th>
                                    <th class="px-3 py-3 text-center font-bold">Total JP</th>
                                    <th class="px-5 py-3 font-bold">% Kehadiran Mapel</th>
                                    <th class="px-5 py-3 font-bold">Simulasi Pemulihan (&ge; 75%)</th>
                                    <th class="px-5 py-3 text-right font-bold">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($peringatanSemester['mapel_items'] as $row)
                                    <tr x-show="cocokNama(@js($row->siswa->nama), @js($row->siswa->no_absen)) && cocokRisiko(@js($row->mapel->kategori)) && cocokMapel(@js($row->mapel->subject_id))"
                                        class="hover:bg-indigo-50/20 transition">
                                        <td class="px-5 py-3 tabular-nums font-bold text-slate-500">{{ $row->siswa->no_absen }}</td>
                                        <td class="px-5 py-3 font-bold text-slate-900">{{ $row->siswa->nama }}</td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="grid h-6 w-6 shrink-0 place-items-center rounded-md text-[10px] font-black text-white" style="background-color: {{ $row->mapel->warna }}">
                                                    {{ $row->mapel->singkatan }}
                                                </span>
                                                <span class="font-bold text-slate-800">{{ $row->mapel->nama }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-center tabular-nums font-bold text-emerald-700">{{ $row->mapel->hadir }}</td>
                                        <td class="px-3 py-3 text-center tabular-nums text-amber-700">{{ $row->mapel->sakit }}</td>
                                        <td class="px-3 py-3 text-center tabular-nums text-blue-700">{{ $row->mapel->izin }}</td>
                                        <td class="px-3 py-3 text-center tabular-nums font-extrabold text-rose-700">{{ $row->mapel->alpa }}</td>
                                        <td class="px-3 py-3 text-center tabular-nums text-violet-700">{{ $row->mapel->dispensasi }}</td>
                                        <td class="px-3 py-3 text-center tabular-nums font-semibold text-slate-600">{{ $row->mapel->total }} JP</td>
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-black tabular-nums text-rose-800">
                                                    {{ $row->mapel->persen }}%
                                                </span>
                                                <span class="text-[10px] font-semibold text-rose-600">(-{{ $row->mapel->selisih_persen }}%)</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span class="inline-flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-[11px] font-bold text-indigo-900">
                                                +{{ $row->mapel->butuh_jp }} JP hadir berturut-turut
                                            </span>
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('admin.rekap', ['jenis' => 'mapel', 'mapel' => $row->mapel->subject_id, 'periode' => 'semester']) }}"
                                                   class="font-bold text-slate-600 hover:text-indigo-600">
                                                    Rekap Mapel
                                                </a>
                                                <span class="text-slate-300">&middot;</span>
                                                <a href="{{ route('admin.rekap', ['jenis' => 'siswa', 'siswa' => $row->siswa->id, 'periode' => 'semester']) }}"
                                                   class="font-bold text-indigo-600 hover:text-indigo-700">
                                                    Siswa &rarr;
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
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
