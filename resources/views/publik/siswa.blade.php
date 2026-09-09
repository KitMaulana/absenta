@extends('layouts.publik')
@section('title', 'Ringkasan Kehadiran &middot; '.$siswa->nama)

@php use App\Enums\AttendanceStatus; @endphp

@section('content')
<div class="space-y-6">
    {{-- Back navigation --}}
    <a href="{{ route('publik.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        <span>Kembali ke Infografis Kelas</span>
    </a>

    {{-- Student Header Card --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="grid h-16 w-16 place-items-center rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 text-xl font-extrabold text-white shadow-md shadow-indigo-600/20 shrink-0">
                    {{ strtoupper(substr($siswa->nama, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-700 border border-indigo-200/60">
                            No. Absen {{ $siswa->no_absen }}
                        </span>
                        <span class="text-xs text-slate-400">Kelas {{ $pengaturan['nama_kelas'] }}</span>
                    </div>
                    <h2 class="mt-1 truncate text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ $siswa->nama }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Periode rekapitulasi: <strong class="text-slate-700">{{ $periodeLabel }}</strong></p>
                </div>
            </div>

            {{-- Period Filter Tabs --}}
            <div class="inline-flex rounded-xl bg-slate-100/80 p-1 border border-slate-200/60 self-start sm:self-auto">
                @foreach (['bulan_ini' => 'Bulan Ini', 'bulan_lalu' => 'Bulan Lalu', 'semester' => 'Semester'] as $kode => $label)
                    <a href="{{ route('publik.siswa', ['siswa' => $siswa, 'periode' => $kode]) }}"
                       class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all
                              {{ $periodePilihan === $kode ? 'bg-white text-slate-900 shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @if ($umum && $umum->total > 0)
        {{-- Attendance Stats Overview --}}
        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <div class="grid h-6 w-6 place-items-center rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-200/60">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 tracking-tight">Presensi Umum Harian</h3>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                {{-- Percentage Hero Card --}}
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 text-center shadow-xs flex flex-col justify-center items-center">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Rasio Kehadiran</span>
                    <p class="mt-2 text-5xl font-black tracking-tight tabular-nums {{ $umum->persen < 75 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ $umum->persen }}%
                    </p>
                    <p class="mt-2 text-xs font-medium text-slate-500">
                        Hadir <strong>{{ $umum->hitung['hadir'] + $umum->hitung['dispensasi'] }}</strong> dari <strong>{{ $umum->total }}</strong> hari efektif tercatat.
                    </p>
                    <div class="mt-4 w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $umum->persen < 75 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $umum->persen }}%"></div>
                    </div>
                </div>

                {{-- Status Breakdown 5 Cards --}}
                <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach (AttendanceStatus::cases() as $status)
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="h-2 w-2 rounded-full" style="background-color: {{ $status->warna() }}"></span>
                                <span class="text-xs font-semibold text-slate-500">{{ $status->label() }}</span>
                            </div>
                            <p class="text-2xl font-extrabold tracking-tight tabular-nums" style="color: {{ $status->warna() }}">
                                {{ $umum->hitung[$status->value] }}
                            </p>
                            <p class="text-[11px] text-slate-400 mt-0.5">hari</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @else
        <div class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center shadow-xs">
            <x-kosong pesan="Belum ada catatan presensi umum untuk siswa ini pada periode {{ $periodeLabel }}." ikon="📅" />
        </div>
    @endif

    {{-- Subject Attendance Breakdown --}}
    @if ($perMapel->isNotEmpty())
        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <div class="grid h-6 w-6 place-items-center rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-200/60">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 tracking-tight">Presensi per Mata Pelajaran</h3>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs divide-y divide-slate-100">
                @foreach ($perMapel as $m)
                    <div class="py-3.5 first:pt-0 last:pb-0">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="h-3 w-3 rounded-full shrink-0" style="background-color: {{ $m->warna }}"></span>
                                <span class="font-bold text-sm text-slate-900 truncate">{{ $m->nama }}</span>
                            </div>
                            <span class="text-sm font-extrabold tabular-nums shrink-0 {{ $m->persen < 75 ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ $m->persen }}%
                            </span>
                        </div>

                        <div class="mt-2.5 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500" style="width: {{ $m->persen }}%; background-color: {{ $m->warna }}"></div>
                        </div>

                        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-400">
                            <span class="text-emerald-700 font-medium">Hadir {{ $m->hadir }}</span>
                            <span>&middot;</span>
                            <span class="text-amber-700 font-medium">Sakit {{ $m->sakit }}</span>
                            <span>&middot;</span>
                            <span class="text-blue-700 font-medium">Izin {{ $m->izin }}</span>
                            <span>&middot;</span>
                            <span class="text-rose-700 font-medium">Alpa {{ $m->alpa }}</span>
                            <span>&middot;</span>
                            <span class="text-violet-700 font-medium">Dispensasi {{ $m->dispensasi }}</span>
                            <span class="ml-auto text-slate-400">Total {{ $m->total }} JP</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Official Report Notice --}}
    <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4 text-xs text-indigo-900 flex items-center justify-between gap-4">
        <div class="flex items-center gap-2.5">
            <div class="grid h-7 w-7 place-items-center rounded-lg bg-indigo-600 text-white shrink-0">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <span>Memerlukan laporan resmi bertanda tangan wali kelas? Silakan hubungi wali kelas untuk pencetakan dokumen PDF A4.</span>
        </div>
    </div>
</div>
@endsection
