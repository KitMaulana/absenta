{{-- Filter periode bersama untuk semua jenis rekap. $ekstra menampung field khusus (pilih mapel / pilih siswa). --}}
<x-card class="mb-5 print:hidden">
    <form method="GET" x-data="{ jenis: '{{ $periode->jenis }}' }" class="flex flex-wrap items-end gap-3">
        <div>
            <label for="periode" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Pilih Periode</label>
            <select id="periode" name="periode" x-model="jenis"
                    onchange="if(this.value === 'bulanan' || this.value === 'semester') this.form.submit()"
                    class="rounded-xl border-slate-200 text-xs font-semibold shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (\App\Support\Periode::PILIHAN as $kode => $label)
                    <option value="{{ $kode }}" @selected($periode->jenis === $kode)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Jika periode bulanan, tampilkan pemilih Bulan & Tahun langsung --}}
        <div x-show="jenis === 'bulanan'">
            <label for="bulan_tahun" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Pilih Bulan &amp; Tahun</label>
            <div class="flex items-center gap-1.5">
                <select id="bulan_tahun" name="bulan_tahun" onchange="this.form.submit()"
                        class="rounded-xl border-slate-200 text-xs font-semibold text-slate-800 shadow-2xs focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                    @foreach ($daftarBulan ?? \App\Support\Periode::daftarBulan() as $b)
                        <option value="{{ $b['kode'] }}" @selected($periode->mulai->format('Y-m') === $b['kode'])>
                            {{ $b['label'] }} @if ($b['has_data']) • Ada Data @endif @if ($b['bulan'] == now()->month && $b['tahun'] == now()->year) (Bulan Ini) @endif
                        </option>
                    @endforeach
                </select>

                {{-- Navigasi Cepat Bulan Sebelumnya & Berikutnya --}}
                <a href="{{ route('admin.rekap', array_merge([$jenis], ['periode' => 'bulanan', 'bulan' => $periode->bulanSebelumnya()->month, 'tahun' => $periode->bulanSebelumnya()->year], request()->only(['mapel', 'siswa']))) }}"
                   class="inline-grid h-8 w-8 place-items-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
                   title="Bulan Sebelumnya">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <a href="{{ route('admin.rekap', array_merge([$jenis], ['periode' => 'bulanan', 'bulan' => $periode->bulanBerikutnya()->month, 'tahun' => $periode->bulanBerikutnya()->year], request()->only(['mapel', 'siswa']))) }}"
                   class="inline-grid h-8 w-8 place-items-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
                   title="Bulan Berikutnya">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <div x-show="jenis !== 'custom' && jenis !== 'bulanan'" x-cloak>
            <label for="acuan" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Tanggal Acuan</label>
            <input id="acuan" name="acuan" type="date" value="{{ $periode->mulai->toDateString() }}"
                   class="rounded-xl border-slate-200 text-xs shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div x-show="jenis === 'custom'" x-cloak>
            <label for="mulai" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Dari Tanggal</label>
            <input id="mulai" name="mulai" type="date" value="{{ $periode->mulai->toDateString() }}"
                   class="rounded-xl border-slate-200 text-xs shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div x-show="jenis === 'custom'" x-cloak>
            <label for="selesai" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Sampai Tanggal</label>
            <input id="selesai" name="selesai" type="date" value="{{ $periode->selesai->toDateString() }}"
                   class="rounded-xl border-slate-200 text-xs shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        {!! $ekstra ?? '' !!}

        <button class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-2xs hover:bg-slate-800 transition">
            Tampilkan
        </button>

        <a href="{{ route('admin.rekap.pdf', array_merge([$jenis], $periode->query(), request()->only(['mapel', 'siswa']))) }}"
           target="_blank"
           class="ml-auto inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-rose-600/25 hover:bg-rose-700 transition">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
            </svg>
            <span>Cetak PDF A4</span>
        </a>
    </form>

    <div class="mt-3.5 border-t border-slate-100 pt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
        <div class="flex items-center gap-2">
            <span>Periode: <strong class="text-slate-800">{{ $periode->label() }}</strong></span>
            <span class="text-slate-300">&bull;</span>
            <span>Total Tercatat: <strong class="text-slate-800">{{ number_format($ringkasan['total']) }}</strong> entri</span>
        </div>
        <div>
            Kehadiran Rata-rata: <strong class="text-emerald-600 text-sm font-extrabold">{{ $ringkasan['persen'] }}%</strong>
        </div>
    </div>
</x-card>
