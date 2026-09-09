@props(['label', 'nilai', 'sub' => null, 'warna' => '#64748b', 'ikon' => null])

<div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p>
            <p class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight tabular-nums" style="color: {{ $warna }}">
                {{ $nilai }}
            </p>
        </div>
        @if ($ikon)
            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-50 text-base shadow-2xs ring-1 ring-slate-200/60 group-hover:scale-105 transition-transform">
                {{ $ikon }}
            </div>
        @endif
    </div>
    @if ($sub)
        <div class="mt-2.5 pt-2.5 border-t border-slate-100 flex items-center gap-1 text-[11px] font-medium text-slate-500">
            <span>{{ $sub }}</span>
        </div>
    @endif
</div>
