@props(['pesan' => 'Belum ada data.', 'ikon' => '📭'])

<div class="flex flex-col items-center justify-center px-4 py-12 text-center">
    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-xl text-slate-500 ring-1 ring-slate-200/60 shadow-2xs">
        {{ $ikon }}
    </div>
    <p class="mt-3 text-sm font-medium text-slate-600 max-w-sm">{{ $pesan }}</p>
    @if ($slot->isNotEmpty())
        <div class="mt-3">
            {{ $slot }}
        </div>
    @endif
</div>
