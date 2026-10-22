@extends('layouts.publik')
@section('title', 'Ringkasan Kehadiran &middot; '.$siswa->nama)

@php use App\Enums\AttendanceStatus; @endphp

@section('content')
<div class="space-y-6">
    {{-- Back navigation --}}
    <a href="{{ route('publik.index') }}" class="inline-flex items-center gap-2 text-xs font-mono font-bold text-cyan-400 hover:text-cyan-300 transition group">
        <svg class="h-4 w-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        <span>KEMBALI KE TELEMETRI KELAS</span>
    </a>

    {{-- Student Header Card --}}
    <section class="cyber-panel rounded-2xl p-6 shadow-xl relative overflow-hidden">
        {{-- Ambient Glow --}}
        <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-cyan-500/10 blur-[60px]"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5 relative">
            <div class="flex items-center gap-4">
                <div class="relative group">
                    <div class="absolute -inset-0.5 rounded-2xl bg-gradient-to-r from-cyan-500 to-indigo-500 opacity-60 blur-sm"></div>
                    <div class="relative grid h-16 w-16 place-items-center rounded-2xl bg-slate-900 font-mono text-xl font-black text-cyan-300 shadow-xl border border-cyan-400/40 shrink-0">
                        {{ strtoupper(substr($siswa->nama, 0, 2)) }}
                    </div>
                </div>

                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-md bg-cyan-950/80 px-2.5 py-0.5 text-xs font-mono font-bold text-cyan-300 border border-cyan-500/40">
                            ABSEN #{{ $siswa->no_absen }}
                        </span>
                        <span class="text-xs font-mono text-slate-400">KELAS {{ $pengaturan['nama_kelas'] }}</span>
                    </div>
                    <h2 class="mt-1.5 truncate text-xl sm:text-2xl font-black text-white tracking-tight font-display">{{ $siswa->nama }}</h2>
                    <p class="text-xs font-mono text-slate-400 mt-0.5">PERIODE TELEMETRI: <strong class="text-cyan-300 font-semibold">{{ $periodeLabel }}</strong></p>
                </div>
            </div>

            {{-- Period Filter Tabs & Month Selector --}}
            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                <div class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900/90 p-1 border border-white/10 shadow-md">
                    <span class="pl-2.5 text-[11px] font-mono font-bold text-slate-400 uppercase tracking-wider">BULAN:</span>
                    <select onchange="window.location.href=this.value"
                            class="rounded-lg border-0 py-1.5 pl-2.5 pr-8 text-xs font-mono font-bold text-cyan-300 bg-slate-950 focus:ring-2 focus:ring-cyan-400 cursor-pointer">
                        @foreach ($daftarBulan as $b)
                            <option value="{{ route('publik.siswa', ['siswa' => $siswa, 'periode' => 'bulanan', 'bulan' => $b['bulan'], 'tahun' => $b['tahun']]) }}"
                                @selected($periodePilihan === 'bulanan' && $selectedBulan == $b['bulan'] && $selectedTahun == $b['tahun'])>
                                {{ $b['label'] }} @if ($b['has_data']) • Ada Data @endif
                            </option>
                        @endforeach
                        <option value="{{ route('publik.siswa', ['siswa' => $siswa, 'periode' => 'semester']) }}"
                            @selected($periodePilihan === 'semester')>
                            Semester Ini
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </section>

    @if ($umum && $umum->total > 0)
        {{-- Attendance Stats Overview --}}
        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <div class="grid h-7 w-7 place-items-center rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 shadow-[0_0_10px_rgba(6,182,212,0.2)]">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-base font-bold text-white tracking-wide font-display uppercase">Presensi Umum Harian</h3>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                {{-- Percentage Hero Card --}}
                <div class="cyber-panel rounded-2xl p-6 text-center shadow-xl flex flex-col justify-center items-center relative overflow-hidden">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-400">RASIO KEHADIRAN</span>
                    <p class="mt-2 text-5xl font-black font-display tracking-tight tabular-nums {{ $umum->persen < 75 ? 'text-rose-400 drop-shadow-[0_0_15px_rgba(244,63,94,0.4)]' : 'text-emerald-400 drop-shadow-[0_0_15px_rgba(16,185,129,0.4)]' }}">
                        {{ $umum->persen }}%
                    </p>
                    <p class="mt-2 text-xs font-sans text-slate-300">
                        Hadir <strong class="text-white font-mono">{{ $umum->hitung['hadir'] + $umum->hitung['dispensasi'] }}</strong> dari <strong class="text-white font-mono">{{ $umum->total }}</strong> hari efektif tercatat.
                    </p>
                    <div class="mt-4 w-full h-2.5 rounded-full bg-slate-950 overflow-hidden border border-white/10">
                        <div class="h-full rounded-full transition-all duration-700 {{ $umum->persen < 75 ? 'bg-rose-500' : 'bg-gradient-to-r from-cyan-400 to-emerald-400 laser-shimmer' }}" style="width: {{ $umum->persen }}%"></div>
                    </div>
                </div>

                {{-- Status Breakdown 5 Cards --}}
                <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach (AttendanceStatus::cases() as $status)
                        <div class="cyber-panel rounded-2xl p-4 shadow-md hover:-translate-y-0.5 transition duration-200">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="h-2 w-2 rounded-full animate-pulse" style="background-color: {{ $status->warna() }}; box-shadow: 0 0 6px {{ $status->warna() }}"></span>
                                <span class="text-xs font-mono font-semibold uppercase text-slate-300">{{ $status->label() }}</span>
                            </div>
                            <p class="font-mono text-2xl sm:text-3xl font-extrabold tracking-tight tabular-nums" style="color: {{ $status->warna() }}; text-shadow: 0 0 10px {{ $status->warna() }}66;">
                                {{ $umum->hitung[$status->value] }}
                            </p>
                            <p class="text-[11px] font-mono text-slate-500 mt-0.5">HARI TERCATAT</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @else
        <div class="cyber-panel rounded-2xl p-8 text-center shadow-xl">
            <x-kosong pesan="Belum ada catatan presensi umum untuk siswa ini pada periode {{ $periodeLabel }}." ikon="📅" />
        </div>
    @endif

    {{-- Subject Attendance Breakdown --}}
    @if ($perMapel->isNotEmpty())
        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <div class="grid h-7 w-7 place-items-center rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 shadow-[0_0_10px_rgba(6,182,212,0.2)]">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                </div>
                <h3 class="text-base font-bold text-white tracking-wide font-display uppercase">Presensi per Mata Pelajaran</h3>
            </div>

            <div class="cyber-panel rounded-2xl p-5 sm:p-6 shadow-xl divide-y divide-white/5">
                @foreach ($perMapel as $m)
                    <div class="py-3.5 first:pt-0 last:pb-0">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="h-3 w-3 rounded-full shrink-0 shadow-sm" style="background-color: {{ $m->warna }}; box-shadow: 0 0 8px {{ $m->warna }}88"></span>
                                <span class="font-bold text-sm text-white truncate">{{ $m->nama }}</span>
                            </div>
                            <span class="font-mono text-sm font-extrabold tabular-nums shrink-0 {{ $m->persen < 75 ? 'text-rose-400' : 'text-cyan-300' }}">
                                {{ $m->persen }}%
                            </span>
                        </div>

                        <div class="mt-2.5 h-2 w-full rounded-full bg-slate-950 overflow-hidden border border-white/10">
                            <div class="h-full rounded-full transition-all duration-500" style="width: {{ $m->persen }}%; background-color: {{ $m->warna }}"></div>
                        </div>

                        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-mono text-slate-400">
                            <span class="text-emerald-400 font-medium">Hadir: {{ $m->hadir }}</span>
                            <span>&bull;</span>
                            <span class="text-amber-400 font-medium">Sakit: {{ $m->sakit }}</span>
                            <span>&bull;</span>
                            <span class="text-cyan-400 font-medium">Izin: {{ $m->izin }}</span>
                            <span>&bull;</span>
                            <span class="text-rose-400 font-medium">Alpa: {{ $m->alpa }}</span>
                            <span>&bull;</span>
                            <span class="text-purple-400 font-medium">Dispensasi: {{ $m->dispensasi }}</span>
                            <span class="ml-auto text-slate-500 font-mono">Total: {{ $m->total }} JP</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Official Report Notice --}}
    <div class="cyber-panel rounded-2xl p-4 text-xs font-mono text-cyan-300 border-cyan-500/30 flex items-center justify-between gap-4 shadow-md">
        <div class="flex items-center gap-2.5">
            <div class="grid h-7 w-7 place-items-center rounded-lg bg-cyan-500/20 text-cyan-300 border border-cyan-400/30 shrink-0">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <span>MEMERLUKAN DOKUMEN REKAP RESMI BERTANDA TANGAN WALI KELAS? SILAKAN HUBUNGI WALI KELAS UNTUK DOKUMEN PDF RESMI.</span>
        </div>
    </div>
</div>
@endsection