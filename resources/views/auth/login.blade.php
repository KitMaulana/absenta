<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Admin &middot; {{ $pengaturan['nama_kelas'] }}</title>
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
                    }
                }
            }
        }
    </script>
</head>
<body class="flex min-h-full items-center justify-center font-sans antialiased bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 px-4 py-12 relative overflow-hidden">
    {{-- Decorative backdrop glows --}}
    <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full bg-indigo-600/15 blur-3xl"></div>
    <div class="pointer-events-none absolute -left-32 -bottom-32 h-96 w-96 rounded-full bg-violet-600/15 blur-3xl"></div>

    <div class="relative w-full max-w-md">
        {{-- Brand / School Header --}}
        <div class="mb-8 text-center">
            @if (!empty($pengaturan['logo']) && file_exists(public_path('storage/'.$pengaturan['logo'])))
                <div class="mx-auto h-16 w-16 rounded-2xl bg-white p-1.5 shadow-xl ring-2 ring-white/20">
                    <img src="{{ asset('storage/'.$pengaturan['logo']) }}" alt="Logo" class="h-full w-full object-contain">
                </div>
            @else
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white shadow-xl shadow-indigo-600/30 ring-2 ring-white/10">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                </div>
            @endif
            <h1 class="mt-4 text-2xl font-black tracking-tight text-white">Portal Admin Presensi</h1>
            <p class="mt-1 text-sm text-slate-400">Kelas {{ $pengaturan['nama_kelas'] }} @if ($pengaturan['nama_sekolah']) &middot; {{ $pengaturan['nama_sekolah'] }} @endif</p>
        </div>

        {{-- Login Card --}}
        <div class="rounded-3xl border border-slate-800/80 bg-slate-900/80 p-7 sm:p-8 shadow-2xl backdrop-blur-xl">
            <div class="mb-6">
                <h2 class="text-base font-bold text-white tracking-tight">Masuk ke Akun Anda</h2>
                <p class="text-xs text-slate-400 mt-0.5">Wali kelas, ketua kelas, dan sekretaris kelas</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 flex items-start gap-2.5 rounded-xl border border-rose-500/30 bg-rose-500/10 p-3.5 text-xs text-rose-300">
                    <svg class="h-4 w-4 shrink-0 text-rose-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300">Alamat Email</label>
                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        </div>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                               placeholder="nama@kelas.test"
                               class="w-full rounded-xl border-slate-700 bg-slate-800/60 pl-10 pr-4 py-2.5 text-sm text-white placeholder-slate-500 shadow-inner focus:border-indigo-500 focus:bg-slate-800 focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300">Kata Sandi</label>
                    <div class="relative mt-1.5">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        </div>
                        <input id="password" name="password" type="password" required
                               placeholder="••••••••"
                               class="w-full rounded-xl border-slate-700 bg-slate-800/60 pl-10 pr-4 py-2.5 text-sm text-white placeholder-slate-500 shadow-inner focus:border-indigo-500 focus:bg-slate-800 focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" value="1"
                               class="rounded border-slate-700 bg-slate-800 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-900">
                        <span class="text-xs text-slate-300">Ingat sesi saya</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/30 hover:from-indigo-500 hover:to-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-all duration-150 mt-2">
                    Masuk ke Sistem
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            <a href="{{ route('publik.index') }}" class="inline-flex items-center gap-1 font-semibold text-indigo-400 hover:text-indigo-300 transition">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                <span>Kembali ke Infografis Publik</span>
            </a>
        </p>
    </div>
</body>
</html>
