@extends('layouts.publik')
@section('title', 'Telemetri Presensi Futuristik')

@php use App\Enums\AttendanceStatus; @endphp

@section('content')

<div class="space-y-8">
    {{-- 1. Ringkasan Kehadiran Hari Ini --}}
    <section>
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2.5">
                <div class="grid h-8 w-8 place-items-center rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 shadow-[0_0_12px_rgba(6,182,212,0.25)]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-black text-white tracking-wide font-display uppercase">
                        Presensi Hari Ini
                    </h2>
                    <p class="text-[11px] text-slate-400 font-mono">LIVE TELEMETRY &bull; STATUS MONITOR</p>
                </div>
            </div>

            @if ($liburHariIni)
                <span class="inline-flex items-center gap-2 rounded-full bg-amber-950/70 px-3 py-1 text-xs font-mono font-semibold text-amber-300 border border-amber-500/40 shadow-[0_0_12px_rgba(245,158,11,0.25)]">
                    <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                    HARI LIBUR
                </span>
            @elseif ($ringkasan['sudah_diinput'])
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-950/70 px-3 py-1 text-xs font-mono font-semibold text-emerald-300 border border-emerald-500/40 shadow-[0_0_12px_rgba(16,185,129,0.3)]">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                    </span>
                    SUDAH DIPERBARUI
                </span>
            @else
                <span class="inline-flex items-center gap-2 rounded-full bg-amber-950/70 px-3 py-1 text-xs font-mono font-semibold text-amber-300 border border-amber-500/40 shadow-[0_0_12px_rgba(245,158,11,0.25)]">
                    <span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
                    MENUNGGU INPUT
                </span>
            @endif
        </div>

        @if ($liburHariIni)
            {{-- Banner Khusus Hari Libur --}}
            <div class="cyber-panel rounded-2xl p-6 sm:p-8 text-amber-200 border-amber-500/40 shadow-[0_0_25px_rgba(245,158,11,0.15)] relative overflow-hidden">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                    <div class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-amber-500/20 text-3xl border border-amber-400/40 shadow-[0_0_15px_rgba(245,158,11,0.3)]">
                        🏖️
                    </div>
                    <div class="space-y-1.5">
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/15 border border-amber-500/30 px-3 py-0.5 text-xs font-mono font-bold text-amber-300">
                            <span>KETETAPAN SISTEM: HARI LIBUR</span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-black text-white tracking-tight font-display">
                            Hari Ini Libur &middot; {{ $liburHariIni->keterangan }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-2xl font-sans">
                            Aktivitas belajar mengajar dinonaktifkan sesuai kalender akademik resmi. Presensi tidak dihitung ke dalam absensi alpa, sakit, maupun izin.
                        </p>
                    </div>
                </div>
            </div>
        @elseif (! $ringkasan['sudah_diinput'])
            <div class="cyber-panel rounded-2xl p-5 text-sm text-amber-200 border-amber-500/30 flex items-start gap-4 shadow-[0_0_20px_rgba(245,158,11,0.1)]">
                <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-amber-500/20 text-amber-400 font-mono font-bold text-base border border-amber-400/30 shadow-[0_0_10px_rgba(245,158,11,0.2)]">
                    !
                </div>
                <div>
                    <h3 class="font-bold text-white font-display text-base">Presensi Hari Ini Belum Diinput</h3>
                    <p class="mt-1 text-xs text-slate-300 leading-relaxed">
                        Data kehadiran harian belum terekam dalam pangkalan data oleh pengurus atau wali kelas. Telemetri akan langsung terbarui secara otomatis begitu proses sinkronisasi absensi selesai.
                    </p>
                </div>
            </div>
        @else
            {{-- 5 Kartu Status Futuristik (Hadir, Sakit, Izin, Alpa, Dispensasi) --}}
            <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-5">
                @php
                    $statusMeta = [
                        'hadir' => [
                            'glow' => 'hover:cyber-glow-emerald hover:border-emerald-500/50',
                            'border' => 'border-emerald-500/30',
                            'badgeBg' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30',
                            'icon' => '<svg class="h-4 w-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/></svg>',
                        ],
                        'sakit' => [
                            'glow' => 'hover:cyber-glow-amber hover:border-amber-500/50',
                            'border' => 'border-amber-500/30',
                            'badgeBg' => 'bg-amber-500/10 text-amber-300 border-amber-500/30',
                            'icon' => '<svg class="h-4 w-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>',
                        ],
                        'izin' => [
                            'glow' => 'hover:cyber-glow-cyan hover:border-cyan-500/50',
                            'border' => 'border-cyan-500/30',
                            'badgeBg' => 'bg-cyan-500/10 text-cyan-300 border-cyan-500/30',
                            'icon' => '<svg class="h-4 w-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>',
                        ],
                        'alpa' => [
                            'glow' => 'hover:cyber-glow-rose hover:border-rose-500/50',
                            'border' => 'border-rose-500/30',
                            'badgeBg' => 'bg-rose-500/10 text-rose-300 border-rose-500/30',
                            'icon' => '<svg class="h-4 w-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>',
                        ],
                        'dispensasi' => [
                            'glow' => 'hover:cyber-glow-purple hover:border-purple-500/50',
                            'border' => 'border-purple-500/30',
                            'badgeBg' => 'bg-purple-500/10 text-purple-300 border-purple-500/30',
                            'icon' => '<svg class="h-4 w-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>',
                        ],
                    ];
                @endphp

                @foreach (AttendanceStatus::cases() as $status)
                    @php
                        $meta = $statusMeta[$status->value] ?? $statusMeta['hadir'];
                    @endphp
                    <div class="cyber-panel relative group overflow-hidden rounded-2xl p-4 text-center transition-all duration-300 hover:-translate-y-1 {{ $meta['glow'] }}">
                        {{-- Top Glowing Laser Accent --}}
                        <div class="absolute top-0 left-0 right-0 h-1" style="background: linear-gradient(90deg, transparent, {{ $status->warna() }}, transparent);"></div>

                        {{-- Corner Tech Marks --}}
                        <div class="absolute top-1.5 left-1.5 w-1 h-1 bg-white/20"></div>
                        <div class="absolute top-1.5 right-1.5 w-1 h-1 bg-white/20"></div>

                        {{-- Icon & Indicator --}}
                        <div class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-lg border backdrop-blur-md {{ $meta['badgeBg'] }}">
                            {!! $meta['icon'] !!}
                        </div>

                        {{-- Giant Digital Number --}}
                        <p class="font-mono text-3xl sm:text-4xl font-extrabold tracking-tight tabular-nums transition-transform duration-200 group-hover:scale-105"
                           style="color: {{ $status->warna() }}; text-shadow: 0 0 15px {{ $status->warna() }}88;">
                            {{ $ringkasan[$status->value] }}
                        </p>

                        {{-- Status Label --}}
                        <p class="mt-1 text-xs font-mono font-semibold uppercase tracking-wider text-slate-300">
                            {{ $status->label() }}
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- Kartu Persentase Kehadiran (Reactor Gauge HUD) --}}
            <div class="mt-4 cyber-panel rounded-2xl p-5 sm:p-6 relative overflow-hidden">
                {{-- Decorative Cyber Grid Background & Glow --}}
                <div class="pointer-events-none absolute -right-20 -bottom-20 h-56 w-56 rounded-full bg-emerald-500/10 blur-[80px]"></div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-md bg-white/5 px-2.5 py-1 text-[11px] font-mono font-bold uppercase tracking-wider text-cyan-300 border border-white/10">
                                <span class="h-1.5 w-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                                Tingkat Kehadiran Kelas
                            </span>
                            <span class="inline-flex items-center rounded-md bg-emerald-950/80 px-2 py-0.5 text-[11px] font-mono font-bold text-emerald-400 border border-emerald-500/30">
                                HARI INI
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-slate-300 font-sans">
                            <strong class="text-white font-mono text-sm">{{ $ringkasan['total'] }}</strong> siswa tercatat dari total 
                            <strong class="text-white font-mono text-sm">{{ $ringkasan['siswa_aktif'] }}</strong> siswa aktif di kelas.
                        </p>
                    </div>

                    <div class="text-left sm:text-right">
                        <span class="font-display text-4xl sm:text-5xl font-black tracking-tight tabular-nums text-emerald-400 drop-shadow-[0_0_20px_rgba(16,185,129,0.4)]">
                            {{ $ringkasan['persen'] }}%
                        </span>
                        <div class="text-[10px] font-mono text-emerald-300/80 tracking-wider uppercase mt-0.5">
                            @if ($ringkasan['persen'] >= 85)
                                ● STATUS: OPTIMAL &bull; TARGET TERCAPAI
                            @elseif ($ringkasan['persen'] >= 75)
                                ● STATUS: STABIL &bull; PERHATIAN
                            @else
                                ● STATUS: WASPADA &bull; TINDAK LANJUT
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Laser Progress Bar Gauge --}}
                <div class="mt-5">
                    <div class="h-3.5 overflow-hidden rounded-full bg-slate-900/90 p-0.5 border border-white/10 ring-1 ring-inset ring-black/50 relative">
                        <div class="h-full rounded-full bg-gradient-to-r from-cyan-400 via-teal-300 to-emerald-400 laser-shimmer transition-all duration-700 shadow-[0_0_15px_rgba(16,185,129,0.6)]"
                             style="width: {{ $ringkasan['persen'] }}%"></div>
                    </div>
                    {{-- Micro Ticks --}}
                    <div class="flex justify-between text-[10px] font-mono text-slate-500 mt-1.5 px-0.5">
                        <span>0%</span>
                        <span>25%</span>
                        <span>50%</span>
                        <span>75%</span>
                        <span>100%</span>
                    </div>
                </div>
            </div>
        @endif
    </section>

    {{-- 2. Daftar Siswa Tidak Hadir (Hanya tampil jika bukan hari libur) --}}
    @if (! $liburHariIni && $tidakHadir->isNotEmpty())
        <section>
            <div class="flex items-center gap-2.5 mb-3">
                <div class="grid h-8 w-8 place-items-center rounded-xl bg-rose-500/10 text-rose-400 border border-rose-500/30 shadow-[0_0_12px_rgba(244,63,94,0.25)]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                </div>
                <h2 class="text-base sm:text-lg font-black text-white tracking-wide font-display uppercase">
                    Siswa Tidak Hadir Hari Ini
                </h2>
                <span class="rounded-full bg-rose-950/80 px-2.5 py-0.5 text-xs font-mono font-bold text-rose-300 border border-rose-500/30 shadow-[0_0_10px_rgba(244,63,94,0.2)]">
                    {{ $tidakHadir->count() }} SISWA
                </span>
            </div>

            <div class="cyber-panel overflow-hidden rounded-2xl border-rose-500/30 shadow-[0_0_20px_rgba(244,63,94,0.1)]">
                <ul class="divide-y divide-white/5">
                    @foreach ($tidakHadir as $t)
                        <li class="flex items-center justify-between gap-3 px-5 py-3.5 hover:bg-rose-500/[0.04] transition duration-200">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="grid h-8 w-8 place-items-center rounded-xl bg-white/5 font-mono text-xs font-bold text-rose-400 border border-rose-500/30 shrink-0">
                                    {{ $t->no_absen }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-white">{{ $t->nama }}</p>
                                    <p class="text-[11px] font-mono text-slate-400">Absen #{{ $t->no_absen }}</p>
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
            <div class="cyber-panel rounded-2xl p-6 text-center border-emerald-500/30 shadow-[0_0_25px_rgba(16,185,129,0.15)] relative overflow-hidden">
                <div class="inline-grid h-12 w-12 place-items-center rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-400/30 shadow-[0_0_15px_rgba(16,185,129,0.3)] mb-2.5">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                </div>
                <h3 class="text-base font-black text-white font-display tracking-wide uppercase">
                    STATUS OPTIMAL: SELURUH SISWA HADIR
                </h3>
                <p class="text-xs font-mono text-emerald-300 mt-1">
                    Tingkat kehadiran kelas hari ini sempurna 100%. Tidak ada catatan ketidakhadiran.
                </p>
            </div>
        </section>
    @endif

    {{-- 3. Rekapitulasi Kehadiran Siswa (Normal & Mapel) --}}
    <section id="rekap-siswa" class="space-y-4">
        {{-- Section Header & Filter Controls --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <div class="grid h-9 w-9 place-items-center rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 shadow-[0_0_12px_rgba(6,182,212,0.25)] shrink-0">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-black text-white tracking-wide font-display uppercase">
                        Rekapitulasi Kehadiran Siswa
                    </h2>
                    <p class="text-xs text-slate-400 font-mono mt-0.5">
                        PRESENSI UMUM &amp; MATA PELAJARAN &middot; <span class="font-bold text-cyan-400 font-mono">{{ $rekapFilterLabel }}</span>
                    </p>
                </div>
            </div>

            {{-- Tactical Filter Segmented Switcher --}}
            <div class="inline-flex rounded-xl bg-slate-900/90 p-1 border border-white/10 self-start md:self-auto shrink-0 shadow-lg">
                @php
                    $filterTabs = [
                        'hari_ini' => 'Hari Ini',
                        'mingguan' => 'Minggu Ini',
                        'bulanan' => 'Pilih Bulan',
                        'semester' => 'Semester Ini',
                    ];
                @endphp
                @foreach ($filterTabs as $kode => $labelTab)
                    <a href="{{ request()->fullUrlWithQuery(['filter_rekap' => $kode, 'page' => 1]) }}#rekap-siswa"
                       class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-mono font-semibold transition-all
                              {{ $filterRekap === $kode ? 'bg-cyan-500/20 text-cyan-300 font-bold border border-cyan-400/40 shadow-[0_0_12px_rgba(6,182,212,0.3)]' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        @if ($kode === 'bulanan')
                            <svg class="h-3.5 w-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @endif
                        <span>{{ $labelTab }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Container Card: Filter, Table & Pagination --}}
        <div class="cyber-panel rounded-2xl shadow-xl overflow-hidden">
            {{-- Toolbar Khusus Filter Bulanan --}}
            @if ($filterRekap === 'bulanan')
                <div class="border-b border-cyan-500/20 bg-slate-900/70 p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="grid h-8 w-8 place-items-center rounded-xl bg-cyan-500/20 text-cyan-300 border border-cyan-400/30 shadow-xs shrink-0">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-mono font-bold text-white uppercase">PILIH BULAN REKAPITULASI</p>
                                <p class="text-[11px] text-slate-400">Tampilkan data presensi seluruh siswa untuk bulan terpilih</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            {{-- Dropdown Bulan Cyber --}}
                            <div class="relative">
                                <select onchange="window.location.href=this.value"
                                        class="rounded-xl border-white/10 text-xs font-mono font-bold text-cyan-300 bg-slate-950 py-1.5 pl-3 pr-8 shadow-md focus:border-cyan-400 focus:ring-cyan-400 cursor-pointer">
                                    @foreach ($daftarBulan as $b)
                                        <option value="{{ request()->fullUrlWithQuery(['filter_rekap' => 'bulanan', 'bulan' => $b['bulan'], 'tahun' => $b['tahun'], 'page' => 1]) }}#rekap-siswa"
                                                @selected($selectedBulan == $b['bulan'] && $selectedTahun == $b['tahun'])>
                                            {{ $b['label'] }} @if ($b['has_data']) • Ada Data @endif @if ($b['bulan'] == now()->month && $b['tahun'] == now()->year) (Bulan Ini) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Tombol Navigasi Cepat Prev / Next Month --}}
                            @php
                                $prevBulan = $bulanTerpilihDate->subMonthNoOverflow();
                                $nextBulan = $bulanTerpilihDate->addMonthNoOverflow();
                            @endphp
                            <a href="{{ request()->fullUrlWithQuery(['filter_rekap' => 'bulanan', 'bulan' => $prevBulan->month, 'tahun' => $prevBulan->year, 'page' => 1]) }}#rekap-siswa"
                               class="inline-grid h-8 w-8 place-items-center rounded-xl bg-slate-950 border border-white/10 text-slate-300 shadow-md hover:bg-cyan-500/20 hover:text-cyan-300 hover:border-cyan-400/40 transition"
                               title="Bulan Sebelumnya ({{ \App\Support\Tanggal::bulanTahun($prevBulan) }})">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['filter_rekap' => 'bulanan', 'bulan' => $nextBulan->month, 'tahun' => $nextBulan->year, 'page' => 1]) }}#rekap-siswa"
                               class="inline-grid h-8 w-8 place-items-center rounded-xl bg-slate-950 border border-white/10 text-slate-300 shadow-md hover:bg-cyan-500/20 hover:text-cyan-300 hover:border-cyan-400/40 transition"
                               title="Bulan Berikutnya ({{ \App\Support\Tanggal::bulanTahun($nextBulan) }})">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Search & Quick Stats Toolbar --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-white/10 bg-slate-950/40 p-4">
                <form method="GET" action="{{ route('publik.index') }}#rekap-siswa" class="relative w-full sm:max-w-xs">
                    <input type="hidden" name="filter_rekap" value="{{ $filterRekap }}">
                    <input type="hidden" name="bulan" value="{{ $selectedBulan }}">
                    <input type="hidden" name="tahun" value="{{ $selectedTahun }}">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </div>
                    <input type="search" name="cari" value="{{ $cariSiswa }}" placeholder="Cari nama siswa..."
                           class="w-full rounded-xl border-white/10 pl-9 pr-8 py-2 text-xs font-mono text-white placeholder:text-slate-500 bg-slate-900/80 shadow-inner focus:border-cyan-400 focus:ring-cyan-400">
                    @if ($cariSiswa !== '')
                        <a href="{{ request()->fullUrlWithQuery(['cari' => null, 'page' => 1]) }}#rekap-siswa"
                           class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-white" title="Reset pencarian">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </form>

                <div class="flex items-center gap-2 text-xs font-mono text-slate-400">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900/80 px-2.5 py-1 text-slate-300 border border-white/10 shadow-xs">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        TOTAL {{ $daftarSiswa->total() }} SISWA AKTIF
                    </span>
                    @if ($cariSiswa !== '')
                        <span class="text-xs text-cyan-300 font-semibold">
                            Hasil: {{ $daftarSiswa->total() }} ditemukan
                        </span>
                    @endif
                </div>
            </div>

            {{-- Quick Stats Bar untuk periode akumulasi (Mingguan / Bulanan / Semester) --}}
            @if ($filterRekap !== 'hari_ini' && $rekapPeriodeRingkasan && $rekapPeriodeRingkasan['total'] > 0)
                <div class="border-b border-white/10 bg-gradient-to-r from-slate-900 via-slate-950 to-slate-900 px-4 py-2.5 flex flex-wrap items-center justify-between gap-3 text-xs font-mono">
                    <div class="flex items-center flex-wrap gap-2.5">
                        <span class="inline-flex items-center gap-1.5 font-bold text-slate-300">
                            <span class="h-2 w-2 rounded-full {{ $rekapPeriodeRingkasan['persen'] >= 85 ? 'bg-emerald-400' : ($rekapPeriodeRingkasan['persen'] >= 75 ? 'bg-amber-400' : 'bg-rose-400') }}"></span>
                            RATA-RATA KEHADIRAN:
                            <span class="font-black text-sm {{ $rekapPeriodeRingkasan['persen'] >= 85 ? 'text-emerald-400' : ($rekapPeriodeRingkasan['persen'] >= 75 ? 'text-amber-400' : 'text-rose-400') }}">
                                {{ $rekapPeriodeRingkasan['persen'] }}%
                            </span>
                        </span>
                        @if ($rekapHariEfektifCount)
                            <span class="text-slate-600 hidden sm:inline">&bull;</span>
                            <span class="inline-flex items-center gap-1 rounded-md bg-white/5 px-2 py-0.5 text-[11px] text-slate-300 border border-white/10">
                                📅 {{ $rekapHariEfektifCount }} HARI EFEKTIF
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center flex-wrap gap-1.5 text-[11px]">
                        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-500/10 px-2 py-0.5 font-bold text-emerald-300 border border-emerald-500/30" title="Total Hadir">
                            H: {{ number_format($rekapPeriodeRingkasan['hadir']) }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-500/10 px-2 py-0.5 font-bold text-amber-300 border border-amber-500/30" title="Total Sakit">
                            S: {{ number_format($rekapPeriodeRingkasan['sakit']) }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-md bg-cyan-500/10 px-2 py-0.5 font-bold text-cyan-300 border border-cyan-500/30" title="Total Izin">
                            I: {{ number_format($rekapPeriodeRingkasan['izin']) }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-md bg-rose-500/10 px-2 py-0.5 font-bold text-rose-300 border border-rose-500/30" title="Total Alpa">
                            A: {{ number_format($rekapPeriodeRingkasan['alpa']) }}
                        </span>
                        @if ($rekapPeriodeRingkasan['dispensasi'] > 0)
                            <span class="inline-flex items-center gap-1 rounded-md bg-purple-500/10 px-2 py-0.5 font-bold text-purple-300 border border-purple-500/30" title="Total Dispensasi">
                                D: {{ number_format($rekapPeriodeRingkasan['dispensasi']) }}
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Table Cyber Data Grid --}}
            @if ($daftarSiswa->isEmpty())
                <div class="p-8 text-center">
                    <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-white/5 text-cyan-400 border border-cyan-500/20 mb-3">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                    </div>
                    <h3 class="text-sm font-bold text-white font-display">Tidak Ada Data Ditemukan</h3>
                    <p class="text-xs text-slate-400 mt-1">Tidak ada data siswa yang cocok dengan kueri &ldquo;{{ $cariSiswa }}&rdquo;.</p>
                    <a href="{{ request()->fullUrlWithQuery(['cari' => null, 'page' => 1]) }}#rekap-siswa"
                       class="mt-3 inline-flex items-center gap-1 text-xs font-mono font-bold text-cyan-400 hover:text-cyan-300">
                        <span>RESET PENCARIAN</span>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12h15m-15 0l6-6m-6 6l6 6"/></svg>
                    </a>
                </div>
            @else
                @if ($liburHariIni && $filterRekap === 'hari_ini')
                    <div class="border-b border-amber-500/20 bg-amber-950/40 px-5 py-2.5 text-xs text-amber-200 flex items-center gap-2 font-mono">
                        <svg class="h-4 w-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        <span>HARI INI BERSTATUS LIBUR ({{ $liburHariIni->keterangan }}). KEGIATAN ABSENSI DINONAKTIFKAN.</span>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/10 text-xs">
                        <thead class="bg-slate-950/80 text-left font-mono uppercase tracking-wider text-cyan-300/80">
                            <tr>
                                <th class="w-12 px-4 py-3.5 font-bold text-center">NO</th>
                                <th class="px-4 py-3.5 font-bold">NAMA SISWA</th>
                                <th class="px-4 py-3.5 font-bold">
                                    @if ($filterRekap === 'hari_ini')
                                        PRESENSI UMUM (HARI INI)
                                    @elseif ($filterRekap === 'semester')
                                        PRESENSI UMUM (SEMESTER)
                                    @else
                                        PRESENSI UMUM ({{ $filterRekap === 'mingguan' ? 'MINGGUAN' : 'BULANAN' }})
                                    @endif
                                </th>
                                <th class="px-4 py-3.5 font-bold">
                                    @if ($filterRekap === 'hari_ini')
                                        MAPEL (HARI INI)
                                    @elseif ($filterRekap === 'semester')
                                        MAPEL (SEMESTER)
                                    @else
                                        MAPEL ({{ $filterRekap === 'mingguan' ? 'MINGGUAN' : 'BULANAN' }})
                                    @endif
                                </th>
                                <th class="px-4 py-3.5 text-right font-bold">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 font-sans">
                            @foreach ($daftarSiswa as $s)
                                @php
                                    $u = $rekapUmumSiswa[$s->id] ?? null;
                                    $m = $rekapMapelSiswa[$s->id] ?? null;
                                @endphp
                                <tr class="hover:bg-cyan-500/[0.04] transition duration-150">
                                    {{-- No Absen --}}
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-grid h-7 w-7 place-items-center rounded-lg bg-white/5 font-mono text-xs font-bold text-cyan-300 border border-white/10 tabular-nums">
                                            {{ $s->no_absen }}
                                        </span>
                                    </td>

                                    {{-- Info Siswa --}}
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="grid h-8 w-8 place-items-center rounded-lg bg-gradient-to-br {{ $s->jenis_kelamin === 'L' ? 'from-cyan-600 to-blue-600' : 'from-rose-500 to-fuchsia-600' }} text-[11px] font-mono font-bold text-white shadow-md shrink-0">
                                                {{ strtoupper(substr($s->nama, 0, 2)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <a href="{{ route('publik.siswa', $s->id) }}" class="font-bold text-white hover:text-cyan-300 transition truncate block">
                                                    {{ $s->nama }}
                                                </a>
                                                <span class="text-[10px] font-mono text-slate-400">
                                                    {{ $s->jenis_kelamin === 'L' ? 'LAKI-LAKI' : 'PEREMPUAN' }} &middot; #{{ $s->no_absen }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Kehadiran Normal --}}
                                    <td class="px-4 py-3">
                                        @if ($filterRekap === 'hari_ini')
                                            @if ($liburHariIni)
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/15 px-2.5 py-0.5 text-xs font-mono font-bold text-amber-300 border border-amber-500/30">
                                                    🏖️ LIBUR
                                                </span>
                                            @elseif ($u && $u->status_hari_ini)
                                                <div class="space-y-1">
                                                    <x-badge :status="$u->status_hari_ini" />
                                                    @if ($u->keterangan_hari_ini)
                                                        <p class="text-[11px] text-slate-400 italic max-w-xs truncate" title="{{ $u->keterangan_hari_ini }}">
                                                            &ldquo;{{ $u->keterangan_hari_ini }}&rdquo;
                                                        </p>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/5 px-2.5 py-0.5 text-xs font-mono font-medium text-slate-400 border border-white/10">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                                                    BELUM DICATAT
                                                </span>
                                            @endif
                                        @else
                                            {{-- Mingguan / Bulanan --}}
                                            @if ($u && $u->total > 0)
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs font-mono font-extrabold tabular-nums {{ $u->persen >= 85 ? 'text-emerald-400' : ($u->persen >= 75 ? 'text-amber-400' : 'text-rose-400') }}">
                                                            {{ $u->persen }}%
                                                        </span>
                                                        <span class="text-[10px] font-mono text-slate-400">({{ $u->counts['hadir'] + $u->counts['dispensasi'] }}/{{ $u->total }} hari)</span>
                                                    </div>
                                                    <div class="mt-1 h-1.5 w-28 bg-slate-950 rounded-full overflow-hidden border border-white/10">
                                                        <div class="h-full {{ $u->persen >= 85 ? 'bg-emerald-400' : ($u->persen >= 75 ? 'bg-amber-400' : 'bg-rose-400') }} rounded-full"
                                                             style="width: {{ $u->persen }}%"></div>
                                                    </div>
                                                    <div class="mt-1 flex items-center gap-1.5 text-[10px] font-mono text-slate-400">
                                                        <span title="Hadir" class="font-bold text-emerald-300">H:{{ $u->counts['hadir'] }}</span>
                                                        <span>&bull;</span>
                                                        <span title="Sakit" class="font-bold text-amber-300">S:{{ $u->counts['sakit'] }}</span>
                                                        <span>&bull;</span>
                                                        <span title="Izin" class="font-bold text-cyan-300">I:{{ $u->counts['izin'] }}</span>
                                                        <span>&bull;</span>
                                                        <span title="Alpa" class="font-bold text-rose-300">A:{{ $u->counts['alpa'] }}</span>
                                                        @if ($u->counts['dispensasi'] > 0)
                                                            <span>&bull;</span>
                                                            <span title="Dispensasi" class="font-bold text-purple-300">D:{{ $u->counts['dispensasi'] }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-slate-500 italic text-[11px] font-mono">Belum ada catatan</span>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Kehadiran Mapel --}}
                                    <td class="px-4 py-3">
                                        @if ($liburHariIni && $filterRekap === 'hari_ini')
                                            <span class="text-amber-300 font-mono font-semibold text-[11px]">Libur (Tidak ada mapel)</span>
                                        @elseif ($m && $m->total > 0)
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center gap-1 rounded-md bg-cyan-950/80 px-2 py-0.5 text-xs font-mono font-bold text-cyan-300 border border-cyan-500/30 tabular-nums">
                                                        {{ $m->hadir }}/{{ $m->total }} JP
                                                    </span>
                                                    <span class="text-xs font-mono font-bold tabular-nums {{ $m->persen >= 85 ? 'text-emerald-400' : ($m->persen >= 75 ? 'text-amber-400' : 'text-rose-400') }}">
                                                        {{ $m->persen }}%
                                                    </span>
                                                </div>
                                                @if ($filterRekap !== 'hari_ini')
                                                    <div class="mt-1 h-1.5 w-24 bg-slate-950 rounded-full overflow-hidden border border-white/10">
                                                        <div class="h-full {{ $m->persen >= 85 ? 'bg-cyan-400' : ($m->persen >= 75 ? 'bg-amber-400' : 'bg-rose-400') }} rounded-full"
                                                             style="width: {{ $m->persen }}%"></div>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-500 italic text-[11px] font-mono">
                                                {{ $filterRekap === 'hari_ini' ? 'Tidak ada jam pelajaran' : 'Belum ada data mapel' }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('publik.siswa', array_filter(['siswa' => $s->id, 'bulan' => $selectedBulan, 'tahun' => $selectedTahun, 'periode' => $filterRekap === 'semester' ? 'semester' : null])) }}"
                                           class="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-mono font-bold text-slate-300 shadow-sm hover:bg-cyan-500/20 hover:border-cyan-400/40 hover:text-cyan-300 transition duration-200 group">
                                            <span>PROFIL</span>
                                            <svg class="h-3.5 w-3.5 text-slate-400 group-hover:text-cyan-300 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-t border-white/10 bg-slate-950/60 px-4 py-3 font-mono">
                    <p class="text-xs text-slate-400">
                        Menampilkan <span class="font-bold text-cyan-300">{{ $daftarSiswa->firstItem() ?? 0 }}</span> - <span class="font-bold text-cyan-300">{{ $daftarSiswa->lastItem() ?? 0 }}</span> dari <span class="font-bold text-cyan-300">{{ $daftarSiswa->total() }}</span> siswa
                    </p>
                    <div class="text-xs">
                        {{ $daftarSiswa->fragment('rekap-siswa')->links() }}
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- 4. Pemilih Periode & Grafik Rekap --}}
    <section id="grafik-section" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <div class="grid h-9 w-9 place-items-center rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 shadow-[0_0_12px_rgba(6,182,212,0.25)] shrink-0">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125-1.125V4.125z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-black text-white tracking-wide font-display uppercase">
                        Visualisasi Telemetri &amp; Grafik
                    </h2>
                    <p class="text-xs text-slate-400 font-mono mt-0.5">
                        KOMPOSISI, MATA PELAJARAN, &amp; TREN HARIAN &middot; <span class="font-bold text-cyan-400 font-mono">{{ $periodeLabel }}</span>
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Pemilih Bulan untuk Grafik --}}
                <div class="inline-flex items-center gap-2 rounded-xl bg-slate-900/90 p-1 border border-white/10 shadow-md">
                    <span class="pl-2.5 text-[11px] font-mono font-bold text-slate-400 uppercase tracking-wider">PERIODE:</span>
                    <select onchange="window.location.href=this.value"
                            class="rounded-lg border-0 py-1.5 pl-2.5 pr-8 text-xs font-mono font-bold text-cyan-300 bg-slate-950 focus:ring-2 focus:ring-cyan-400 cursor-pointer">
                        @foreach ($daftarBulan as $b)
                            <option value="{{ request()->fullUrlWithQuery(['periode' => 'bulanan', 'bulan' => $b['bulan'], 'tahun' => $b['tahun'], 'filter_rekap' => $filterRekap]) }}#grafik-section"
                                @selected($periodePilihan === 'bulanan' && $selectedBulan == $b['bulan'] && $selectedTahun == $b['tahun'])>
                                {{ $b['label'] }} @if ($b['has_data']) • Ada Data @endif @if ($b['bulan'] == now()->month && $b['tahun'] == now()->year) (Bulan Ini) @endif
                            </option>
                        @endforeach
                        <option value="{{ request()->fullUrlWithQuery(['periode' => 'semester', 'filter_rekap' => $filterRekap]) }}#grafik-section"
                            @selected($periodePilihan === 'semester')>
                            Semester Ini
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            {{-- Grafik Donut Komposisi --}}
            <div class="cyber-panel rounded-2xl p-5 sm:p-6 shadow-xl">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-mono font-bold text-white uppercase tracking-wider">KOMPOSISI KEHADIRAN</h3>
                    <span class="text-xs font-mono text-cyan-400">{{ $periodeLabel }}</span>
                </div>
                @if ($komposisi['total'] === 0)
                    <x-kosong pesan="Belum ada catatan presensi pada periode {{ $periodeLabel }}." ikon="📊" />
                @else
                    <div class="relative mt-3 h-64">
                        <canvas id="grafikDonut"></canvas>
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-xs font-mono">
                        <span class="text-slate-400">RATA-RATA KEHADIRAN:</span>
                        <span class="font-extrabold text-sm text-emerald-400 drop-shadow-[0_0_8px_rgba(16,185,129,0.5)]">
                            {{ $komposisi['persen'] }}%
                        </span>
                    </div>
                @endif
            </div>

            {{-- Grafik Batang Mapel --}}
            <div class="cyber-panel rounded-2xl p-5 sm:p-6 shadow-xl">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-mono font-bold text-white uppercase tracking-wider">KEHADIRAN PER MATA PELAJARAN</h3>
                    <span class="text-xs font-mono text-cyan-400">{{ $periodeLabel }}</span>
                </div>
                @if ($perMapel->isEmpty())
                    <x-kosong pesan="Belum ada data absensi mata pelajaran pada {{ $periodeLabel }}." ikon="📚" />
                @else
                    <div class="relative mt-3 h-64">
                        <canvas id="grafikMapel"></canvas>
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/10 text-[11px] font-mono text-slate-400">
                        *Persentase dihitung dari jam pelajaran terlaksana pada {{ $periodeLabel }}.
                    </div>
                @endif
            </div>
        </div>

        {{-- Grafik Tren Garis --}}
        <div class="cyber-panel rounded-2xl p-5 sm:p-6 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                <div>
                    <h3 class="text-sm font-mono font-bold text-white uppercase tracking-wider">TREN KEHADIRAN HARIAN</h3>
                    <p class="text-xs font-mono text-slate-400 mt-0.5">Persentase kehadiran pada setiap hari sekolah aktif ({{ $periodeLabel }})</p>
                </div>
                @if (!empty($tren['labels']))
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-cyan-950/80 px-2.5 py-1 text-[11px] font-mono font-bold text-cyan-300 border border-cyan-500/30 self-start sm:self-auto shadow-xs">
                        <span class="h-1.5 w-1.5 rounded-full bg-cyan-400"></span>
                        {{ count($tren['labels']) }} HARI EFEKTIF TERCATAT
                    </span>
                @endif
            </div>
            @if (empty($tren['labels']))
                <x-kosong pesan="Belum ada data absensi harian pada periode {{ $periodeLabel }}." ikon="📉" />
            @else
                <div class="relative mt-3 h-64 sm:h-72">
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
            borderColor: '#0f172a',
            hoverOffset: 6
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
                    color: '#94a3b8',
                    font: { family: "'JetBrains Mono', monospace", size: 11, weight: '600' },
                    padding: 15
                }
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                titleColor: '#22d3ee',
                bodyColor: '#e2e8f0',
                borderColor: 'rgba(6, 182, 212, 0.4)',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 10,
                callbacks: { label: c => ` ${c.label}: ${c.parsed} entri` }
            },
        },
    },
});
@endif

@if (! $perMapel->isEmpty())
@php
    $mapelFullNames = $perMapel->pluck('nama')->all();
@endphp
new Chart(document.getElementById('grafikMapel'), {
    type: 'bar',
    data: {
        labels: @json($perMapel->pluck('singkatan')),
        datasets: [{
            label: '% Kehadiran',
            data: @json($perMapel->pluck('persen')),
            backgroundColor: @json($perMapel->pluck('warna')),
            borderRadius: 8,
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
                    color: '#94a3b8',
                    callback: v => v + '%',
                    font: { family: "'JetBrains Mono', monospace", size: 10 }
                },
                grid: { color: 'rgba(255, 255, 255, 0.08)' }
            },
            x: {
                grid: { display: false },
                ticks: { 
                    color: '#cbd5e1',
                    font: { family: "'JetBrains Mono', monospace", size: 11, weight: '700' } 
                }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                titleColor: '#22d3ee',
                bodyColor: '#e2e8f0',
                borderColor: 'rgba(6, 182, 212, 0.4)',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 10,
                callbacks: {
                    title: c => {
                        const idx = c[0].dataIndex;
                        const fullNames = @json($mapelFullNames);
                        return fullNames[idx] || c[0].label;
                    },
                    label: c => ` ${c.parsed.y}% kehadiran siswa`
                }
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
            borderColor: '#22d3ee',
            backgroundColor: 'rgba(34, 211, 238, 0.12)',
            borderWidth: 2.5,
            pointRadius: 3,
            pointHoverRadius: 6,
            pointBackgroundColor: '#00f2fe',
            pointBorderColor: '#0891b2',
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
                    color: '#94a3b8',
                    callback: v => v + '%',
                    font: { family: "'JetBrains Mono', monospace", size: 10 }
                },
                grid: { color: 'rgba(255, 255, 255, 0.08)' }
            },
            x: {
                grid: { display: false },
                ticks: {
                    color: '#94a3b8',
                    font: { family: "'JetBrains Mono', monospace", size: 10 },
                    maxRotation: 0,
                    autoSkip: true,
                    maxTicksLimit: 12
                }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                titleColor: '#22d3ee',
                bodyColor: '#e2e8f0',
                borderColor: 'rgba(6, 182, 212, 0.4)',
                borderWidth: 1,
                padding: 12,
                cornerRadius: 10,
                callbacks: { label: c => ` ${c.parsed.y}% kehadiran siswa` }
            },
        },
    },
});
@endif
</script>
@endpush
@endsection