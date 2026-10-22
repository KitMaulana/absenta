@props(['pesan' => 'Belum ada data.', 'ikon' => '📭'])

<div class="flex flex-col items-center justify-center px-4 py-12 text-center">
    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-white/5 text-xl text-cyan-400 ring-1 ring-white/10 shadow-lg border border-cyan-500/20">
        {{ $ikon }}
    </div>
    <p class="mt-3 text-sm font-medium text-slate-300 max-w-sm">{{ $pesan }}</p>
    @if ($slot->isNotEmpty())
        <div class="mt-3">
            {{ $slot }}
        </div>
    @endif
</div>