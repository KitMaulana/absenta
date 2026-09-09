@extends('layouts.admin')
@section('title', $judul)
@section('subtitle', $periode->label())

@section('content')
@include('admin.rekap._filter')

<div class="rounded-2xl border border-indigo-200 bg-indigo-50/70 p-4 text-xs text-indigo-900 print:hidden flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs">
    <div class="flex items-start gap-3">
        <div class="grid h-8 w-8 place-items-center rounded-xl bg-indigo-600 text-white shrink-0">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
        </div>
        <div>
            <p class="font-bold text-sm text-indigo-950">Panduan Pencetakan Infografis</p>
            <p class="mt-0.5 text-indigo-800">
                Gunakan <strong>Cetak PDF A4</strong> (tombol merah di atas) untuk format arsip resmi dompdf, atau gunakan <strong>Print Browser</strong> (di kanan) untuk mencetak langsung dengan visualisasi Chart.js penuh warna.
            </p>
        </div>
    </div>
    <button onclick="window.print()" class="inline-flex items-center gap-1.5 shrink-0 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-md shadow-indigo-600/25 hover:bg-indigo-700 transition">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
        <span>Print Lembar Browser</span>
    </button>
</div>

<div id="lembar-cetak" class="mt-5 space-y-6">
    <header class="rounded-2xl border border-slate-200/80 bg-white p-6 text-center shadow-xs">
        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Infografis Kehadiran Kelas {{ $pengaturan['nama_kelas'] }}</h2>
        <p class="text-sm font-semibold text-indigo-600 mt-0.5">{{ $pengaturan['nama_sekolah'] }}</p>
        <p class="mt-1.5 text-xs text-slate-500">
            {{ $periode->label() }} &middot; T.A. {{ $pengaturan['tahun_ajaran'] }} (Semester {{ $pengaturan['semester'] }}) &middot; {{ $jumlahHariEfektif }} Hari Efektif
        </p>
    </header>

    <div class="grid gap-4 sm:grid-cols-3 xl:grid-cols-6">
        <x-stat label="Tingkat Hadir" :nilai="$ringkasan['persen'].'%'" warna="#16a34a" sub="Rata-rata periode">
            <x-slot:ikon>
                <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
            </x-slot:ikon>
        </x-stat>
        @foreach (\App\Enums\AttendanceStatus::cases() as $status)
            <x-stat :label="$status->label()" :nilai="$ringkasan[$status->value]" :warna="$status->warna()" sub="Akumulasi entri" />
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-card judul="Komposisi Kehadiran">
            <div class="h-72"><canvas id="grafikDonut"></canvas></div>
        </x-card>

        <x-card judul="Kehadiran per Mata Pelajaran">
            @if ($perMapel->isEmpty())
                <x-kosong pesan="Belum ada data absensi mata pelajaran." ikon="📚" />
            @else
                <div class="h-72"><canvas id="grafikMapel"></canvas></div>
            @endif
        </x-card>
    </div>

    <x-card judul="Tren Kehadiran Harian">
        @if (empty($tren['labels']))
            <x-kosong pesan="Belum ada data harian untuk digambar grafiknya." ikon="📉" />
        @else
            <div class="h-72"><canvas id="grafikTren"></canvas></div>
        @endif
    </x-card>

    <x-card padat>
        <x-slot:judul>Peringkat &amp; Rekapitulasi Siswa</x-slot:judul>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-xs">
                <thead class="bg-slate-50/80 text-left uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="w-12 px-4 py-3 font-bold text-center">No</th>
                        <th class="px-4 py-3 font-bold">Nama Lengkap Siswa</th>
                        <th class="px-3 py-3 text-center font-bold">Alpa</th>
                        <th class="px-4 py-3 text-right font-bold">% Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($baris->sortByDesc('persen') as $b)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-2.5 text-center tabular-nums font-bold text-slate-400">{{ $b->siswa->no_absen }}</td>
                            <td class="px-4 py-2.5 font-bold text-slate-900">{{ $b->siswa->nama }}</td>
                            <td class="px-3 py-2.5 text-center tabular-nums {{ $b->hitung['alpa'] > 0 ? 'font-black text-rose-600' : 'text-slate-400' }}">{{ $b->hitung['alpa'] }}</td>
                            <td class="px-4 py-2.5 text-right tabular-nums font-extrabold {{ $b->persen < 75 ? 'text-rose-600' : 'text-slate-900' }}">{{ $b->persen }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
</div>

@push('head')
@verbatim
<style>
@media print {
    @page { size: A4 portrait; margin: 12mm; }
    body { background: #fff !important; }
    aside, header.sticky, .print\:hidden { display: none !important; }
    .lg\:pl-72 { padding-left: 0 !important; }
    main { padding: 0 !important; max-width: 100% !important; }
    #lembar-cetak section { break-inside: avoid; box-shadow: none !important; border: 1px solid #cbd5e1 !important; }
}
</style>
@endverbatim
@endpush

@push('scripts')
<script>
const warnaStatus = @json(collect(\App\Enums\AttendanceStatus::cases())->map->warna()->all());

new Chart(document.getElementById('grafikDonut'), {
    type: 'doughnut',
    data: {
        labels: @json(collect(\App\Enums\AttendanceStatus::cases())->map->label()->all()),
        datasets: [{
            data: @json(collect(\App\Enums\AttendanceStatus::cases())->map(fn ($s) => $ringkasan[$s->value])->all()),
            backgroundColor: warnaStatus,
            borderWidth: 2,
            borderColor: '#ffffff',
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: false,
        cutout: '70%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 10,
                    boxHeight: 10,
                    usePointStyle: true,
                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '500' }
                }
            }
        },
    },
});

@if (! $perMapel->isEmpty())
new Chart(document.getElementById('grafikMapel'), {
    type: 'bar',
    data: {
        labels: @json($perMapel->pluck('singkatan')),
        datasets: [{
            label: '% Kehadiran',
            data: @json($perMapel->pluck('persen')),
            backgroundColor: @json($perMapel->pluck('warna')),
            borderRadius: 6,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: false,
        scales: {
            y: { min: 0, max: 100, ticks: { callback: v => v + '%' }, grid: { color: 'rgba(226, 232, 240, 0.6)' } },
            x: { grid: { display: false } }
        },
        plugins: { legend: { display: false } },
    },
});
@endif

@if (! empty($tren['labels']))
new Chart(document.getElementById('grafikTren'), {
    type: 'line',
    data: {
        labels: @json($tren['labels']),
        datasets: [{
            label: '% Kehadiran',
            data: @json($tren['persen']),
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79, 70, 229, 0.08)',
            borderWidth: 2.5,
            pointRadius: 2,
            tension: 0.35,
            fill: true,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: false,
        scales: {
            y: { min: 0, max: 100, ticks: { callback: v => v + '%' }, grid: { color: 'rgba(226, 232, 240, 0.6)' } },
            x: { grid: { display: false } }
        },
        plugins: { legend: { display: false } },
    },
});
@endif
</script>
@endpush
@endsection
