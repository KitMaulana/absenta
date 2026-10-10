@php
    $baseLink = 'group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold tracking-tight transition-all duration-150';
    $activeLink = 'bg-gradient-to-r from-indigo-600 to-indigo-700 text-white shadow-md shadow-indigo-950/40 font-bold';
    $inactiveLink = 'text-slate-300 hover:bg-slate-800/80 hover:text-white';
    $iconClass = 'h-4 w-4 shrink-0 transition-transform duration-150 group-hover:scale-110';
@endphp

{{-- Brand Header --}}
@php
    $logoApp = (!empty($pengaturan['logo']) && file_exists(public_path('storage/'.$pengaturan['logo'])))
        ? asset('storage/'.$pengaturan['logo'])
        : (file_exists(public_path('logo-smancir.png')) ? asset('logo-smancir.png') : asset('favicon.png'));
@endphp
<div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-800/80 px-5 bg-slate-950/40">
    <div class="h-10 w-10 rounded-xl bg-white p-1 shadow-sm ring-1 ring-white/20 shrink-0 flex items-center justify-center">
        <img src="{{ $logoApp }}" alt="Logo SMAN 1 Ciruas" class="h-full w-full object-contain">
    </div>
    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-bold text-white tracking-tight">{{ $pengaturan['nama_kelas'] }}</p>
        <p class="truncate text-[11px] font-medium text-slate-400">T.A. {{ $pengaturan['tahun_ajaran'] }} &middot; {{ $pengaturan['semester'] }}</p>
    </div>
</div>

{{-- Navigation Links --}}
<nav class="flex flex-1 flex-col overflow-y-auto px-3.5 py-4 space-y-6 scrollbar-thin scrollbar-thumb-slate-800">
    {{-- Grup: Absensi --}}
    <div>
        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Presensi Kelas</p>
        <div class="space-y-1">
            <a href="{{ route('admin.dashboard') }}"
               class="{{ $baseLink }} {{ request()->routeIs('admin.dashboard') ? $activeLink : $inactiveLink }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.absensi-umum.index') }}"
               class="{{ $baseLink }} {{ request()->routeIs('admin.absensi-umum.*') ? $activeLink : $inactiveLink }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Absensi Umum Harian</span>
            </a>

            <a href="{{ route('admin.absensi-mapel.index') }}"
               class="{{ $baseLink }} {{ request()->routeIs('admin.absensi-mapel.*') ? $activeLink : $inactiveLink }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                </svg>
                <span>Absensi per Mapel</span>
            </a>
        </div>
    </div>

    {{-- Grup: Laporan --}}
    <div>
        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Rekap & Laporan</p>
        <div class="space-y-1">
            <a href="{{ route('admin.rekap', 'umum') }}"
               class="{{ $baseLink }} {{ request()->routeIs('admin.rekap') && request()->route('jenis') === 'umum' ? $activeLink : $inactiveLink }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                </svg>
                <span>Rekap Umum</span>
            </a>

            <a href="{{ route('admin.rekap', 'mapel') }}"
               class="{{ $baseLink }} {{ request()->routeIs('admin.rekap') && request()->route('jenis') === 'mapel' ? $activeLink : $inactiveLink }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                </svg>
                <span>Rekap per Mapel</span>
            </a>

            <a href="{{ route('admin.rekap', 'siswa') }}"
               class="{{ $baseLink }} {{ request()->routeIs('admin.rekap') && request()->route('jenis') === 'siswa' ? $activeLink : $inactiveLink }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                </svg>
                <span>Rekap per Siswa</span>
            </a>

            <a href="{{ route('admin.rekap', 'bulanan') }}"
               class="{{ $baseLink }} {{ request()->routeIs('admin.rekap') && request()->route('jenis') === 'bulanan' ? $activeLink : $inactiveLink }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
                <span>Laporan Bulanan</span>
            </a>

            <a href="{{ route('admin.rekap', 'semester') }}"
               class="{{ $baseLink }} {{ request()->routeIs('admin.rekap') && request()->route('jenis') === 'semester' ? $activeLink : $inactiveLink }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                </svg>
                <span>Laporan Semester</span>
            </a>

            <a href="{{ route('admin.rekap', 'infografis') }}"
               class="{{ $baseLink }} {{ request()->routeIs('admin.rekap') && request()->route('jenis') === 'infografis' ? $activeLink : $inactiveLink }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"/>
                </svg>
                <span>Infografis Cetak</span>
            </a>
        </div>
    </div>

    {{-- Grup: Master Data --}}
    <div>
        <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Master Data</p>
        <div class="space-y-1">
            <a href="{{ route('admin.siswa.index') }}"
               class="{{ $baseLink }} {{ request()->routeIs('admin.siswa.*') ? $activeLink : $inactiveLink }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                </svg>
                <span>Data Siswa</span>
            </a>

            <a href="{{ route('admin.mapel.index') }}"
               class="{{ $baseLink }} {{ request()->routeIs('admin.mapel.*') ? $activeLink : $inactiveLink }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z"/>
                </svg>
                <span>Mata Pelajaran</span>
            </a>

            <a href="{{ route('admin.jadwal.index') }}"
               class="{{ $baseLink }} {{ request()->routeIs('admin.jadwal.*') ? $activeLink : $inactiveLink }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Jadwal Pelajaran</span>
            </a>

            <a href="{{ route('admin.libur.index') }}"
               class="{{ $baseLink }} {{ request()->routeIs('admin.libur.*') ? $activeLink : $inactiveLink }}">
                <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
                <span>Hari Libur</span>
            </a>
        </div>
    </div>

    {{-- Grup: Khusus Wali Kelas --}}
    @if (auth()->user()->isWaliKelas())
        <div>
            <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Wali Kelas</p>
            <div class="space-y-1">
                <a href="{{ route('admin.pengaturan.edit') }}"
                   class="{{ $baseLink }} {{ request()->routeIs('admin.pengaturan.*') ? $activeLink : $inactiveLink }}">
                    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Pengaturan Kelas</span>
                </a>

                <a href="{{ route('admin.admins.index') }}"
                   class="{{ $baseLink }} {{ request()->routeIs('admin.admins.*') ? $activeLink : $inactiveLink }}">
                    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                    </svg>
                    <span>Manajemen Admin</span>
                </a>
            </div>
        </div>
    @endif
</nav>

{{-- User Profile & Actions Footer --}}
<div class="shrink-0 border-t border-slate-800/80 p-3 bg-slate-950/40">
    <div class="flex items-center gap-3 rounded-xl p-2 bg-slate-800/50 border border-slate-700/50">
        <div class="grid h-8 w-8 place-items-center rounded-lg bg-indigo-600 font-bold text-xs text-white shrink-0">
            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        </div>
        <div class="min-w-0 flex-1">
            <p class="truncate text-xs font-bold text-white">{{ auth()->user()->name }}</p>
            <p class="truncate text-[10px] text-slate-400 font-medium">{{ auth()->user()->roleLabel() }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Keluar"
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-700/80 hover:text-red-400 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                </svg>
            </button>
        </form>
    </div>
</div>
