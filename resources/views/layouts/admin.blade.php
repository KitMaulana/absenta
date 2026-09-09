<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') &middot; {{ $pengaturan['nama_kelas'] }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🗓️</text></svg>">

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
<body class="h-full font-sans text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">
<div x-data="{ sidebar: false }" class="min-h-full">

    {{-- Sidebar mobile --}}
    <div x-show="sidebar" x-cloak class="fixed inset-0 z-40 lg:hidden">
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="sidebar = false"></div>
        <aside class="relative flex h-full w-72 flex-col bg-slate-900 border-r border-slate-800 text-slate-200 shadow-2xl">
            @include('partials.admin-nav')
        </aside>
    </div>

    {{-- Sidebar desktop --}}
    <aside class="hidden lg:fixed lg:inset-y-0 lg:z-30 lg:flex lg:w-72 lg:flex-col bg-slate-900 border-r border-slate-800/80 text-slate-200">
        @include('partials.admin-nav')
    </aside>

    <div class="lg:pl-72">
        <header class="sticky top-0 z-20 flex h-16 items-center justify-between gap-4 border-b border-slate-200/80 bg-white/85 px-4 backdrop-blur-md sm:px-6 lg:px-8 shadow-xs">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" class="lg:hidden -m-2 p-2 rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition" @click="sidebar = true" aria-label="Buka menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
                <div class="min-w-0">
                    <h1 class="truncate text-base font-bold text-slate-900 tracking-tight">@yield('title', 'Admin')</h1>
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <svg class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="truncate">@yield('subtitle', \App\Support\Tanggal::panjang(now()))</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('publik.index') }}" target="_blank"
                   class="hidden sm:inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-indigo-600 transition">
                    <svg class="h-3.5 w-3.5 text-slate-400 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Web Publik</span>
                </a>

                <div class="h-7 w-px bg-slate-200 hidden sm:block"></div>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block leading-tight">
                        <p class="text-xs font-bold text-slate-900">{{ auth()->user()->name }}</p>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/60 mt-0.5">
                            {{ auth()->user()->roleLabel() }}
                        </span>
                    </div>
                    <div class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-xs font-bold text-white shadow-sm ring-2 ring-white">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                </div>
            </div>
        </header>

        <main class="px-4 py-6 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <x-flash />
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
<style>[x-cloak]{display:none!important}</style>
</body>
</html>
