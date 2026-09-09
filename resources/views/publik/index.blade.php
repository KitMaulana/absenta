@extends('layouts.publik')
@section('title', 'Infografis Kehadiran')

@php use App\Enums\AttendanceStatus; @endphp

@section('content')

<div class="space-y-8">
    {{-- 1. Ringkasan Kehadiran Hari Ini --}}
    <section>
        <div class="flex items-center justify-between gap-3 mb-3">
            <div class="flex items-center gap-2">
                <div class="grid h-6 w-6 place-items-center rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-200/60">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Presensi Hari Ini</h2>
            </div>
            @if ($ringkasan['sudah_diinput'])
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200/60">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Sudah Diperbarui
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 border border-amber-200/60">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Menunggu Input
                </span>
            @endif
        </div>

        @if (! $ringkasan['sudah_diinput'])
            <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-5 text-sm text-amber-900 flex items-start gap-3.5 shadow-xs backdrop-blur">
                <div class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-amber-500 text-white font-bold text-sm shadow-xs">
                    !
                </div>
                <div>
                    <h3 class="font-bold text-amber-950">Presensi Hari Ini Belum Diinput</h3>
                    <p class="mt-0.5 text-xs text-amber-800 leading-relaxed">
                        Data kehadiran hari ini belum dimasukkan oleh pengurus atau wali kelas. Informasi akan otomatis terbarui di sini segera setelah absensi tercatat.
                    </p>
                </div>
            </div>
        @else
            {{-- Kartu Angka 5 Status --}}
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                @foreach (AttendanceStatus::cases() as $status)
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 text-center shadow-xs transition-all hover:shadow-md hover:-translate-y-0.5">
                        <div class="mx-auto flex h-2 w-8 rounded-full mb-3" style="background-color: {{ $status->warna() }}"></div>
                        <p class="text-3xl font-extrabold tracking-tight tabular-nums" style="color: {{ $status->warna() }}">{{ $ringkasan[$status->value] }}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">{{ $status->label() }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Kartu Persentase Kehadiran --}}
            <div class="mt-4 rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tingkat Kehadiran Kelas</span>
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700">Hari Ini</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            <strong>{{ $ringkasan['total'] }}</strong> siswa tercatat dari total <strong>{{ $ringkasan['siswa_aktif'] }}</strong> siswa aktif di kelas.
                        </p>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-4xl font-extrabold tracking-tight tabular-nums text-emerald-600">{{ $ringkasan['persen'] }}%</span>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="h-3 overflow-hidden rounded-full bg-slate-100 p-0.5 ring-1 ring-inset ring-slate-200/50">
                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-500 shadow-xs" style="width: {{ $ringkasan['persen'] }}%"></div>
                    </div>
                </div>
            </div>
        @endif
    </section>

    {{-- 2. Daftar Siswa Tidak Hadir --}}
    @if ($tidakHadir->isNotEmpty())
        <section>
            <div class="flex items-center gap-2 mb-3">
                <div class="grid h-6 w-6 place-items-center rounded-lg bg-rose-50 text-rose-600 border border-rose-200/60">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                </div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Siswa Tidak Hadir Hari Ini</h2>
                <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-bold text-rose-800">{{ $tidakHadir->count() }} siswa</span>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                <ul class="divide-y divide-slate-100">
                    @foreach ($tidakHadir as $t)
                        <li class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-slate-50/70 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="grid h-8 w-8 place-items-center rounded-xl bg-slate-100 text-xs font-bold text-slate-700 shrink-0">
                                    {{ $t->no_absen }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $t->nama }}</p>
                                    <p class="text-[11px] text-slate-400">Absen #{{ $t->no_absen }}</p>
                                </div>
                            </div>
                            <x-badge :status="$t->status" />
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @elseif ($ringkasan['sudah_diinput'])
        <section>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-5 text-center shadow-xs backdrop-blur">
                <div class="inline-grid h-10 w-10 place-items-center rounded-xl bg-emerald-600 text-white shadow-xs mb-2">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                </div>
                <h3 class="text-sm font-bold text-emerald-950">Seluruh Siswa Hadir Hari Ini</h3>
                <p class="text-xs text-emerald-800 mt-0.5">Tingkat kehadiran kelas hari ini sempurna 100%.</p>
            </div>
        </section>
    @endif

    {{-- 3. Rekapitulasi Kehadiran Siswa (Normal & Mapel) --}}
    <section id="rekap-siswa" class="space-y-4">
        {{-- Section Header & Filter Controls --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <div class="grid h-8 w-8 place-items-center rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-200/60 shadow-2xs shrink-0">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Rekapitulasi Kehadiran Siswa</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Status presensi normal & mata pelajaran &middot; <span class="font-semibold text-indigo-600">{{ $rekapFilterLabel }}</span></p>
                </div>
            </div>

            {{-- Filter Segmented Pills --}}
            <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200/60 self-start md:self-auto shrink-0 shadow-2xs">
                @php
                    $filterTabs = [
                        'hari_ini' => 'Hari Ini',
                        'mingguan' => 'Minggu Ini',
                        'bulanan' => 'Bulan Ini',
                    ];
                @endphp
                @foreach ($filterTabs as $kode => $labelTab)
                    <a href="{{ request()->fullUrlWithQuery(['filter_rekap' => $kode, 'page' => 1]) }}#rekap-siswa"
                       class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all
                              {{ $filterRekap === $kode ? 'bg-white text-indigo-600 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                        <span>{{ $labelTab }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Container Card: Filter, Table & Pagination --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white shadow-xs overflow-hidden">
            {{-- Search & Quick Stats Toolbar --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/50 p-4">
                <form method="GET" action="{{ route('publik.index') }}#rekap-siswa" class="relative w-full sm:max-w-xs">
                    <input type="hidden" name="filter_rekap" value="{{ $filterRekap }}">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </div>
                    <input type="search" name="cari" value="{{ $cariSiswa }}" placeholder="Cari nama siswa..."
                           class="w-full rounded-xl border-slate-200 pl-9 pr-8 py-2 text-xs shadow-2xs placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                    @if ($cariSiswa !== '')
                        <a href="{{ request()->fullUrlWithQuery(['cari' => null, 'page' => 1]) }}#rekap-siswa"
                           class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600" title="Reset pencarian">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </form>

                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white px-2.5 py-1 font-medium text-slate-700 border border-slate-200/80 shadow-2xs">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Total {{ $daftarSiswa->total() }} Siswa Aktif
                    </span>
                    @if ($cariSiswa !== '')
                        <span class="text-xs text-indigo-600 font-semibold">
                            Hasil: {{ $daftarSiswa->total() }} ditemukan
                        </span>
                    @endif
                </div>
            </div>

            {{-- Table --}}
            @if ($daftarSiswa->isEmpty())
                <div class="p-8 text-center">
                    <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400 mb-3">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Tidak ada siswa ditemukan</h3>
                    <p class="text-xs text-slate-500 mt-1">Tidak ada data siswa yang cocok dengan &ldquo;{{ $cariSiswa }}&rdquo;.</p>
                    <a href="{{ request()->fullUrlWithQuery(['cari' => null, 'page' => 1]) }}#rekap-siswa"
                       class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-700">
                        <span>Reset Pencarian</span>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12h15m-15 0l6-6m-6 6l6 6"/></svg>
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-xs">
                        <thead class="bg-slate-50/80 text-left uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="w-12 px-4 py-3.5 font-bold text-center">No</th>
                                <th class="px-4 py-3.5 font-bold">Nama Siswa</th>
                                <th class="px-4 py-3.5 font-bold">
                                    @if ($filterRekap === 'hari_ini')
                                        Kehadiran Normal Hari Ini
                                    @else
                                        Kehadiran Normal ({{ $filterRekap === 'mingguan' ? 'Mingguan' : 'Bulanan' }})
                                    @endif
                                </th>
                                <th class="px-4 py-3.5 font-bold">
                                    @if ($filterRekap === 'hari_ini')
                                        Kehadiran Mapel Hari Ini
                                    @else
                                        Kehadiran Mapel ({{ $filterRekap === 'mingguan' ? 'Mingguan' : 'Bulanan' }})
                                    @endif
                                </th>
                                <th class="px-4 py-3.5 text-right font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($daftarSiswa as $s)
                                @php
                                    $u = $rekapUmumSiswa[$s->id] ?? null;
                                    $m = $rekapMapelSiswa[$s->id] ?? null;
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    {{-- No Absen --}}
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-xs font-bold text-slate-700 tabular-nums">
                                            {{ $s->no_absen }}
                                        </span>
                                    </td>

                                    {{-- Info Siswa --}}
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="grid h-8 w-8 place-items-center rounded-lg bg-gradient-to-br {{ $s->jenis_kelamin === 'L' ? 'from-indigo-600 to-sky-600' : 'from-rose-500 to-pink-600' }} text-[11px] font-bold text-white shadow-2xs shrink-0">
                                                {{ strtoupper(substr($s->nama, 0, 2)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <a href="{{ route('publik.siswa', $s->id) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition truncate block">
                                                    {{ $s->nama }}
                                                </a>
                                                <span class="text-[10px] text-slate-400 font-medium">
                                                    {{ $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }} &middot; Absen #{{ $s->no_absen }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kehadiran Normal --}}
                                    <td class="px-4 py-3">
                                        @if ($filterRekap === 'hari_ini')
                                            @if ($u && $u->status_hari_ini)
                                                <div class="space-y-1">
                                                    <x-badge :status="$u->status_hari_ini" />
                                                    @if ($u->keterangan_hari_ini)
                                                        <p class="text-[11px] text-slate-500 italic max-w-xs truncate" title="{{ $u->keterangan_hari_ini }}">
                                                            &ldquo;{{ $u->keterangan_hari_ini }}&rdquo;
                                                        </p>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500 ring-1 ring-inset ring-slate-200/80">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                                    Belum dicatat
                                                </span>
                                            @endif
                                        @else
                                            {{-- Mingguan / Bulanan --}}
                                            @if ($u && $u->total > 0)
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs font-extrabold tabular-nums {{ $u->persen >= 85 ? 'text-emerald-600' : ($u->persen >= 75 ? 'text-amber-600' : 'text-rose-600') }}">
                                                            {{ $u->persen }}%
                                                        </span>
                                                        <span class="text-[10px] text-slate-400">({{ $u->counts['hadir'] + $u->counts['dispensasi'] }}/{{ $u->total }} hari)</span>
                                                    </div>
                                                    <div class="mt-1 h-1.5 w-28 bg-slate-100 rounded-full overflow-hidden">
                                                        <div class="h-full {{ $u->persen >= 85 ? 'bg-emerald-500' : ($u->persen >= 75 ? 'bg-amber-500' : 'bg-rose-500') }} rounded-full"
                                                             style="width: {{ $u->persen }}%"></div>
                                                    </div>
                                                    <div class="mt-1 flex items-center gap-1.5 text-[10px] text-slate-500">
                                                        <span title="Hadir" class="font-semibold text-emerald-700">H: {{ $u->counts['hadir'] }}</span>
                                                        <span>&middot;</span>
                                                        <span title="Sakit" class="font-semibold text-amber-600">S: {{ $u->counts['sakit'] }}</span>
                                                        <span>&middot;</span>
                                                        <span title="Izin" class="font-semibold text-blue-600">I: {{ $u->counts['izin'] }}</span>
                                                        <span>&middot;</span>
                                                        <span title="Alpa" class="font-semibold text-rose-600">A: {{ $u->counts['alpa'] }}</span>
                                                        @if ($u->counts['dispensasi'] > 0)
                                                            <span>&middot;</span>
                                                            <span title="Dispensasi" class="font-semibold text-violet-600">D: {{ $u->counts['dispensasi'] }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-slate-400 italic text-[11px]">Belum ada catatan</span>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Kehadiran Mapel --}}
                                    <td class="px-4 py-3">
                                        @if ($m && $m->total > 0)
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-700 border border-indigo-200/60 tabular-nums">
                                                        {{ $m->hadir }}/{{ $m->total }} JP
                                                    </span>
                                                    <span class="text-xs font-bold tabular-nums {{ $m->persen >= 85 ? 'text-emerald-600' : ($m->persen >= 75 ? 'text-amber-600' : 'text-rose-600') }}">
                                                        {{ $m->persen }}%
                                                    </span>
                                                </div>
                                                @if ($filterRekap !== 'hari_ini')
                                                    <div class="mt-1 h-1.5 w-24 bg-slate-100 rounded-full overflow-hidden">
                                                        <div class="h-full {{ $m->persen >= 85 ? 'bg-indigo-500' : ($m->persen >= 75 ? 'bg-amber-500' : 'bg-rose-500') }} rounded-full"
                                                             style="width: {{ $m->persen }}%"></div>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">
                                                {{ $filterRekap === 'hari_ini' ? 'Tidak ada jam pelajaran' : 'Belum ada data mapel' }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('publik.siswa', $s->id) }}"
                                           class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo-600 transition group">
                                            <span>Profil</span>
                                            <svg class="h-3.5 w-3.5 text-slate-400 group-hover:text-indigo-600 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Footer --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/50 px-4 py-3">
                    <p class="text-xs text-slate-500 font-medium">
                        Menampilkan <span class="font-bold text-slate-700">{{ $daftarSiswa->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-700">{{ $daftarSiswa->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-700">{{ $daftarSiswa->total() }}</span> siswa
                    </p>
                    <div>
                        {{ $daftarSiswa->fragment('rekap-siswa')->links() }}
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- 4. Pemilih Periode & Grafik --}}
    <section>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div class="flex items-center gap-2">
                <div class="grid h-6 w-6 place-items-center rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-200/60">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                </div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">Grafik Rekap &middot; {{ $periodeLabel }}</h2>
            </div>

            <div class="inline-flex rounded-xl bg-white p-1 border border-slate-200/80 shadow-2xs">
                @foreach (['bulan_ini' => 'Bulan Ini', 'bulan_lalu' => 'Bulan Lalu', 'semester' => 'Semester'] as $kode => $label)
                    <a href="{{ route('publik.index', ['periode' => $kode]) }}"
                       class="rounded-lg px-3.5 py-1.5 text-xs font-semibold transition-all
                              {{ $periodePilihan === $kode ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            {{-- Grafik Donut Komposisi --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-slate-900">Komposisi Kehadiran</h3>
                    <span class="text-xs font-semibold text-slate-400">{{ $periodeLabel }}</span>
                </div>
                @if ($komposisi['total'] === 0)
                    <x-kosong pesan="Belum ada catatan presensi pada periode ini." ikon="📊" />
                @else
                    <div class="relative mt-3 h-64">
                        <canvas id="grafikDonut"></canvas>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-500">Tingkat Kehadiran Periode:</span>
                        <span class="font-extrabold text-sm text-emerald-600">{{ $komposisi['persen'] }}%</span>
                    </div>
                @endif
            </div>

            {{-- Grafik Batang Mapel --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-bold text-slate-900">Kehadiran per Mata Pelajaran</h3>
                    <span class="text-xs font-semibold text-slate-400">{{ $periodeLabel }}</span>
                </div>
                @if ($perMapel->isEmpty())
                    <x-kosong pesan="Belum ada data absensi mata pelajaran." ikon="📚" />
                @else
                    <div class="relative mt-3 h-64">
                        <canvas id="grafikMapel"></canvas>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-400">
                        *Persentase dihitung dari jam pelajaran yang telah terlaksana.
                    </div>
                @endif
            </div>
        </div>

        {{-- Grafik Tren Garis 30 Hari --}}
        <div class="mt-5 rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Tren Kehadiran Harian</h3>
                    <p class="text-xs text-slate-400 mt-0.5">30 hari kalender terakhir</p>
                </div>
            </div>
            @if (empty($tren['labels']))
                <x-kosong pesan="Belum ada data historis untuk digambar." ikon="📉" />
            @else
                <div class="relative mt-3 h-64">
                    <canvas id="grafikTren"></canvas>
                </div>
            @endif
        </div>
    </section>
</div>

@push('scripts')
<script>
function cariSiswa() {
    return {
        kata: '',
        hasil: [],
        memuat: false,

        async cari() {
            if (this.kata.trim().length < 2) {
                this.hasil = [];
                return;
            }

            this.memuat = true;
            try {
                const res = await fetch('{{ route('publik.cari') }}?q=' + encodeURIComponent(this.kata));
                this.hasil = (await res.json()).data;
            } finally {
                this.memuat = false;
            }
        },
    };
}

@if ($komposisi['total'] > 0)
new Chart(document.getElementById('grafikDonut'), {
    type: 'doughnut',
    data: {
        labels: @json(collect(AttendanceStatus::cases())->map->label()->all()),
        datasets: [{
            data: @json(collect(AttendanceStatus::cases())->map(fn ($s) => $komposisi[$s->value])->all()),
            backgroundColor: @json(collect(AttendanceStatus::cases())->map->warna()->all()),
            borderWidth: 2,
            borderColor: '#ffffff',
            hoverOffset: 4
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 10,
                    boxHeight: 10,
                    usePointStyle: true,
                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '500' },
                    padding: 15
                }
            },
            tooltip: {
                backgroundColor: '#0f172a',
                padding: 10,
                cornerRadius: 8,
                callbacks: { label: c => ` ${c.label}: ${c.parsed} entri` }
            },
        },
    },
});
@endif

@if (! $perMapel->isEmpty())
new Chart(document.getElementById('grafikMapel'), {
    type: 'bar',
    data: {
        labels: @json($perMapel->pluck('singkatan')),
        datasets: [{
            label: '% Kehadiran',
            data: @json($perMapel->pluck('persen')),
            backgroundColor: @json($perMapel->pluck('warna')),
            borderRadius: 6,
            borderSkipped: false,
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
                ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 10, weight: '600' } }
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
@endif

@if (! empty($tren['labels']))
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
@endif
</script>
@endpush
@endsection
