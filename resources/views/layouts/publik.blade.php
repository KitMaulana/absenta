<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $logoApp = (!empty($pengaturan['logo']) && file_exists(public_path('storage/'.$pengaturan['logo'])))
            ? asset('storage/'.$pengaturan['logo'])
            : (file_exists(public_path('logo-smancir.png')) ? asset('logo-smancir.png') : asset('favicon.png'));
    @endphp
    <title>@yield('title', 'Infografis Absensi') &middot; {{ $pengaturan['nama_kelas'] }}</title>
    <meta name="description" content="Informasi kehadiran kelas {{ $pengaturan['nama_kelas'] }} yang diperbarui otomatis dari input admin kelas.">
    <link rel="icon" type="image/png" href="{{ $logoApp }}">
    <link rel="apple-touch-icon" href="{{ $logoApp }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    @stack('head')
</head>
<body class="flex min-h-full flex-col font-sans text-slate-800 antialiased selection:bg-indigo-500 selection:text-white bg-slate-50/80">

{{-- Hero Header --}}
<header class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-white shadow-xl">
    {{-- Decorative subtle glow --}}
    <div class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-indigo-500/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -left-20 -bottom-20 h-72 w-72 rounded-full bg-violet-500/15 blur-3xl"></div>

    <div class="relative mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="h-18 w-18 sm:h-20 sm:w-20 shrink-0 rounded-2xl bg-white p-2 shadow-2xl ring-2 ring-white/20 backdrop-blur transition-transform hover:scale-105 flex items-center justify-center">
                    <img src="{{ $logoApp }}" alt="Logo SMAN 1 Ciruas" class="h-full w-full object-contain">
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-500/20 px-2.5 py-0.5 text-[11px] font-bold text-indigo-300 ring-1 ring-inset ring-indigo-500/30">
                            <span class="h-1.5 w-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                            Portal Presensi
                        </span>
                        @if ($pengaturan['nama_sekolah'])
                            <span class="truncate text-xs text-slate-300 font-semibold tracking-wide uppercase">{{ $pengaturan['nama_sekolah'] }}</span>
                        @endif
                    </div>
                    <h1 class="mt-1.5 truncate text-2xl font-black sm:text-3xl tracking-tight text-white drop-shadow-xs">
                        Absensi Kelas {{ $pengaturan['nama_kelas'] }}
                    </h1>
                    <p class="mt-1 text-xs text-slate-300 flex flex-wrap items-center gap-x-2.5 gap-y-1">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="text-slate-400">Wali kelas:</span>
                            <strong class="text-white font-semibold">{{ $pengaturan['nama_wali_kelas'] }}</strong>
                        </span>
                        <span class="text-slate-500">&bull;</span>
                        <span class="inline-flex items-center gap-1 rounded-md bg-white/10 px-2 py-0.5 text-[11px] text-slate-200">
                            T.A. {{ $pengaturan['tahun_ajaran'] }} ({{ $pengaturan['semester'] }})
                        </span>
                    </p>
                </div>
            </div>

            <div class="flex sm:flex-col items-center sm:items-end justify-between gap-3 shrink-0 pt-2 sm:pt-0 border-t border-white/10 sm:border-t-0">
                <div class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-slate-200 backdrop-blur-md ring-1 ring-white/10 shadow-xs">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <svg class="h-4 w-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ \App\Support\Tanggal::panjang(now()) }}</span>
                </div>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-indigo-600/30 hover:bg-indigo-500 hover:shadow-indigo-500/40 transition">
                        <span>Buka Dashboard Admin</span>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3.5 py-1.5 text-xs font-bold text-indigo-200 ring-1 ring-white/20 hover:bg-white/20 hover:text-white transition">
                        <span>Masuk Admin</span>
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</header>

<main class="mx-auto w-full max-w-5xl flex-1 px-4 py-8 sm:px-6">
    @yield('content')
</main>

{{-- Modern Footer --}}
<footer class="mt-auto border-t border-slate-200 bg-white shadow-xs">
    <div class="mx-auto max-w-5xl px-4 py-8 text-center text-xs text-slate-500 sm:px-6">
        <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-slate-600 mb-3">
            <svg class="h-3.5 w-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">Sistem Terintegrasi &middot; Data Diperbarui Real-Time</span>
        </div>
        <p class="max-w-xl mx-auto leading-relaxed">
            Halaman ini adalah infografis publik resmi kelas {{ $pengaturan['nama_kelas'] }}.
            Untuk menjaga privasi siswa, data identitas sensitif seperti NISN dan nomor kontak tidak dipublikasikan.
        </p>
        <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-slate-400">
            <p>&copy; {{ date('Y') }} Absensi {{ $pengaturan['nama_kelas'] }}. Hak cipta dilindungi.</p>
            @auth
                <a href="{{ route('admin.dashboard') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 transition">
                    Buka Dashboard Admin &rarr;
                </a>
            @else
                <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 transition">
                    Portal Masuk Wali &amp; Pengurus Kelas &rarr;
                </a>
            @endauth
        </div>
    </div>
</footer>

<style>[x-cloak]{display:none!important}</style>
@stack('scripts')
</body>
</html>
