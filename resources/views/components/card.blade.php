@props(['judul' => null, 'aksi' => null, 'padat' => false])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200/80 bg-white shadow-xs transition-all duration-150']) }}>
    @if ($judul || $aksi)
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/40 px-5 py-3.5 sm:px-6 rounded-t-2xl">
            <h2 class="text-sm font-bold tracking-tight text-slate-900">{{ $judul }}</h2>
            @if ($aksi)<div class="flex flex-wrap items-center gap-2">{{ $aksi }}</div>@endif
        </header>
    @endif
    <div class="{{ $padat ? '' : 'p-5 sm:p-6' }}">{{ $slot }}</div>
</section>
