<!DOCTYPE html>
<html lang="id" class="h-full bg-[#070b14]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $logoApp = (!empty($pengaturan['logo']) && file_exists(public_path('storage/'.$pengaturan['logo'])))
            ? asset('storage/'.$pengaturan['logo'])
            : (file_exists(public_path('logo-smancir.png')) ? asset('logo-smancir.png') : asset('favicon.png'));
    @endphp
    <title>@yield('title', 'Portal Presensi Futuristik') &middot; {{ $pengaturan['nama_kelas'] }}</title>
    <meta name="description" content="Sistem Presensi Digital & Telemetri Kehadiran Kelas {{ $pengaturan['nama_kelas'] }} SMAN 1 Ciruas.">
    <link rel="icon" type="image/png" href="{{ $logoApp }}">
    <link rel="apple-touch-icon" href="{{ $logoApp }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
                        display: ['"Space Grotesk"', '"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        cyber: {
                            50: '#ecfeff',
                            100: '#cffafe',
                            200: '#a5f3fc',
                            300: '#67e8f9',
                            400: '#22d3ee',
                            500: '#06b6d4',
                            600: '#0891b2',
                            700: '#0e7490',
                            800: '#155e75',
                            900: '#164e63',
                        },
                        neon: {
                            cyan: '#00f2fe',
                            blue: '#4facfe',
                            purple: '#a855f7',
                            emerald: '#10b981',
                            amber: '#f59e0b',
                            rose: '#f43f5e',
                        },
                        dark: {
                            bg: '#070b14',
                            card: '#0f172a',
                            border: 'rgba(255, 255, 255, 0.08)',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        /* Futuristic Background Canvas */
        .cyber-bg {
            background-color: #070b14;
            background-image: 
                radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.18) 0%, transparent 45%),
                radial-gradient(circle at 90% 25%, rgba(6, 182, 212, 0.12) 0%, transparent 35%),
                radial-gradient(circle at 10% 75%, rgba(168, 85, 247, 0.12) 0%, transparent 35%),
                linear-gradient(to right, rgba(255, 255, 255, 0.025) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.025) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 100% 100%, 36px 36px, 36px 36px;
        }

        /* Glassmorphism Classes */
        .cyber-panel {
            background: rgba(13, 19, 36, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.45);
        }

        .cyber-panel:hover {
            border-color: rgba(6, 182, 212, 0.35);
        }

        .cyber-glow-cyan {
            box-shadow: 0 0 25px -4px rgba(6, 182, 212, 0.3);
        }

        .cyber-glow-emerald {
            box-shadow: 0 0 25px -4px rgba(16, 185, 129, 0.3);
        }

        .cyber-glow-amber {
            box-shadow: 0 0 25px -4px rgba(245, 158, 11, 0.3);
        }

        .cyber-glow-rose {
            box-shadow: 0 0 25px -4px rgba(244, 63, 94, 0.3);
        }

        .cyber-glow-purple {
            box-shadow: 0 0 25px -4px rgba(168, 85, 247, 0.3);
        }

        /* Laser shimmer animation for progress bars */
        @keyframes laser-sweep {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        .laser-shimmer {
            position: relative;
            overflow: hidden;
        }
        .laser-shimmer::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transform: translateX(-100%);
            animation: laser-sweep 2.5s infinite;
        }

        /* Scanline Overlay for Sci-Fi feel */
        .cyber-scanline {
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.03), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.03));
            background-size: 100% 3px, 6px 100%;
            pointer-events: none;
        }

        /* Smooth scrollbars */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #090d18;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #0ea5e9;
        }
    </style>
    @stack('head')
</head>
<body class="flex min-h-full flex-col font-sans text-slate-100 antialiased selection:bg-cyan-500 selection:text-black cyber-bg">

{{-- Futuristic Hero Header HUD --}}
<header class="relative border-b border-cyan-500/20 bg-slate-950/80 backdrop-blur-xl shadow-2xl">
    {{-- Ambient Laser Beams --}}
    <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-cyan-500/15 blur-[100px]"></div>
    <div class="pointer-events-none absolute -left-24 -bottom-24 h-96 w-96 rounded-full bg-indigo-600/15 blur-[100px]"></div>
    <div class="pointer-events-none absolute left-1/2 top-0 -translate-x-1/2 h-px w-3/4 bg-gradient-to-r from-transparent via-cyan-400/50 to-transparent"></div>

    <div class="relative mx-auto max-w-6xl px-4 py-7 sm:px-6 sm:py-9">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            
            {{-- Left: Logo & Class Branding --}}
            <div class="flex items-center gap-5">
                {{-- Holographic Logo Frame --}}
                <div class="relative group shrink-0">
                    <div class="absolute -inset-1 rounded-2xl bg-gradient-to-r from-cyan-500 via-indigo-500 to-fuchsia-500 opacity-60 blur-md group-hover:opacity-100 transition duration-500 group-hover:duration-200 animate-pulse"></div>
                    <div class="relative h-20 w-20 sm:h-22 sm:w-22 rounded-2xl bg-slate-900/90 p-2.5 shadow-2xl ring-1 ring-cyan-400/40 backdrop-blur flex items-center justify-center overflow-hidden">
                        <img src="{{ $logoApp }}" alt="Logo SMAN 1 Ciruas" class="h-full w-full object-contain filter drop-shadow-[0_0_8px_rgba(255,255,255,0.4)] transition-transform duration-300 group-hover:scale-110">
                        {{-- Corner Tech Accents --}}
                        <div class="absolute top-1 left-1 w-1.5 h-1.5 border-t border-l border-cyan-400"></div>
                        <div class="absolute top-1 right-1 w-1.5 h-1.5 border-t border-r border-cyan-400"></div>
                        <div class="absolute bottom-1 left-1 w-1.5 h-1.5 border-b border-l border-cyan-400"></div>
                        <div class="absolute bottom-1 right-1 w-1.5 h-1.5 border-b border-r border-cyan-400"></div>
                    </div>
                </div>

                {{-- Class Information --}}
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-cyan-950/80 px-3 py-0.5 text-[11px] font-mono font-bold text-cyan-300 border border-cyan-500/40 shadow-[0_0_12px_rgba(6,182,212,0.3)]">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-400"></span>
                            </span>
                            PORTAL PRESENSI TELEMETRI
                        </span>
                        @if ($pengaturan['nama_sekolah'])
                            <span class="rounded-md bg-white/5 px-2 py-0.5 text-[11px] font-mono uppercase tracking-wider text-slate-300 border border-white/10">
                                {{ $pengaturan['nama_sekolah'] }}
                            </span>
                        @endif
                    </div>
                    
                    <h1 class="mt-2 text-2xl font-black sm:text-4xl tracking-tight text-white font-display drop-shadow-[0_0_20px_rgba(255,255,255,0.25)] flex items-center gap-3">
                        <span class="bg-gradient-to-r from-white via-cyan-100 to-indigo-200 bg-clip-text text-transparent">
                            Absensi Kelas {{ $pengaturan['nama_kelas'] }}
                        </span>
                    </h1>

                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-slate-300">
                        <span class="inline-flex items-center gap-1.5 bg-slate-900/60 px-2.5 py-1 rounded-lg border border-white/5">
                            <span class="text-slate-400 font-mono text-[11px]">WALI KELAS:</span>
                            <strong class="text-cyan-300 font-semibold tracking-wide">{{ $pengaturan['nama_wali_kelas'] }}</strong>
                        </span>
                        <span class="text-slate-600">&bull;</span>
                        <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-950/50 px-2.5 py-1 text-[11px] font-mono text-indigo-300 border border-indigo-500/30">
                            T.A. {{ $pengaturan['tahun_ajaran'] }} &bull; {{ $pengaturan['semester'] }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Right: Live Digital Clock & HUD Telemetry Status --}}
            <div class="flex flex-col sm:items-end justify-between gap-3 shrink-0 pt-4 md:pt-0 border-t border-white/10 md:border-t-0">
                
                {{-- Live Real-time Clock & Date --}}
                <div class="flex items-center gap-2.5 bg-slate-900/80 px-4 py-2 rounded-xl border border-cyan-500/30 shadow-[0_0_15px_rgba(6,182,212,0.15)] backdrop-blur-md">
                    <div class="flex flex-col text-right">
                        <span id="liveClock" class="font-mono text-xs sm:text-sm font-bold text-cyan-300 tracking-wider">
                            --:--:-- WIB
                        </span>
                        <span class="text-[11px] text-slate-400 font-medium">
                            {{ \App\Support\Tanggal::panjang(now()) }}
                        </span>
                    </div>
                    <div class="h-8 w-8 rounded-lg bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400 shrink-0">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Action / Auth Button --}}
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="group relative inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 px-4 py-2 text-xs font-mono font-bold text-white shadow-lg shadow-cyan-500/25 hover:shadow-cyan-500/40 hover:scale-[1.02] transition-all border border-cyan-300/30">
                        <span class="h-2 w-2 rounded-full bg-white animate-ping"></span>
                        <span>TERMINAL ADMIN</span>
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="group relative inline-flex items-center gap-2 rounded-xl bg-slate-900/90 px-4 py-2 text-xs font-mono font-bold text-cyan-300 ring-1 ring-cyan-500/40 hover:ring-cyan-400 hover:bg-cyan-950/60 hover:text-white hover:shadow-[0_0_15px_rgba(6,182,212,0.3)] transition-all">
                        <span>MASUK ADMIN</span>
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</header>

{{-- Main Page Body --}}
<main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6">
    @yield('content')
</main>

{{-- Futuristic Cyber Footer --}}
<footer class="mt-auto border-t border-cyan-500/15 bg-slate-950/80 backdrop-blur-xl shadow-2xl">
    <div class="mx-auto max-w-6xl px-4 py-8 text-center sm:px-6">
        <div class="inline-flex items-center gap-2.5 rounded-full bg-cyan-950/60 px-4 py-1.5 text-xs text-cyan-300 border border-cyan-500/30 mb-4 shadow-[0_0_15px_rgba(6,182,212,0.15)] font-mono">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
            </span>
            <span>TELEMETRI AKTIF &bull; REAL-TIME ENCRYPTED REPLICATION</span>
        </div>
        
        <p class="max-w-xl mx-auto text-xs text-slate-400 leading-relaxed font-sans">
            Sistem Informasi Presensi Digital Resmi Kelas {{ $pengaturan['nama_kelas'] }}.
            Menampilkan data kehadiran komprehensif, telemetri mata pelajaran, dan riwayat presensi berkala.
        </p>

        <div class="mt-6 pt-5 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 font-mono">
            <p>&copy; {{ date('Y') }} PRESENSI {{ $pengaturan['nama_kelas'] }} &bull; SMAN 1 CIRUAS. ALL RIGHTS RESERVED.</p>
            @auth
                <a href="{{ route('admin.dashboard') }}" class="font-semibold text-cyan-400 hover:text-cyan-300 hover:underline transition flex items-center gap-1">
                    <span>PANEL KONTROL ADMIN</span>
                    <span>&rarr;</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="font-semibold text-cyan-400 hover:text-cyan-300 hover:underline transition flex items-center gap-1">
                    <span>PORTAL AUTENTIKASI PENGURUS</span>
                    <span>&rarr;</span>
                </a>
            @endauth
        </div>
    </div>
</footer>

<script>
    // Live ticking digital clock
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const clockEl = document.getElementById('liveClock');
        if (clockEl) {
            clockEl.textContent = `${hours}:${minutes}:${seconds} WIB`;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

@stack('scripts')
</body>
</html>