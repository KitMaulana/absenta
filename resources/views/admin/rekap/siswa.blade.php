@extends('layouts.admin')
@section('title', $judul)
@section('subtitle', $periode->label())

@section('content')
@include('admin.rekap._filter', ['ekstra' => view('admin.rekap._pilih-siswa', ['daftarSiswa' => $daftarSiswa, 'siswa' => $siswa])])

@if (! $siswa)
    <x-card><x-kosong pesan="Belum ada siswa aktif." ikon="🧑‍🎓" /></x-card>
@else
<div class="grid gap-6 lg:grid-cols-3">
    {{-- Profil Kehadiran Siswa --}}
    <x-card judul="Profil Kehadiran Siswa">
        <div class="flex items-center gap-3.5 mb-4">
            <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 font-extrabold text-white text-base shadow-md shadow-indigo-600/20">
                {{ strtoupper(substr($siswa->nama, 0, 2)) }}
            </div>
            <div>
                <p class="text-base font-extrabold text-slate-900 tracking-tight">{{ $siswa->nama }}</p>
                <p class="text-xs text-slate-500 font-medium">No. Absen {{ $siswa->no_absen }} &middot; {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
            </div>
        </div>

        @if ($umum)
            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 text-center">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Rasio Presensi Umum</span>
                <p class="text-4xl font-black mt-1 {{ $umum->persen < 75 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $umum->persen }}%</p>
                <p class="text-[11px] text-slate-500 mt-0.5">dari {{ $umum->total }} hari efektif tercatat</p>
            </div>

            <div class="mt-4 grid grid-cols-5 gap-1.5 text-center">
                @foreach (\App\Enums\AttendanceStatus::cases() as $status)
                    <div class="rounded-xl border border-slate-200/80 bg-white py-2 shadow-2xs">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $status->kode() }}</span>
                        <span class="mt-0.5 block text-base font-extrabold tabular-nums" style="color: {{ $status->warna() }}">{{ $umum->hitung[$status->value] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>

    {{-- Tabel Per Mapel Siswa --}}
    <x-card class="lg:col-span-2" padat>
        <x-slot:judul>Kehadiran per Mata Pelajaran</x-slot:judul>

        @if ($perMapel->isEmpty())
            <x-kosong pesan="Belum ada riwayat absensi mata pelajaran untuk siswa ini pada periode terpilih." ikon="📚" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-xs">
                    <thead class="bg-slate-50/80 text-left uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-bold">Mata Pelajaran</th>
                            <th class="px-3 py-3 text-center font-bold text-emerald-800 bg-emerald-50/40">H</th>
                            <th class="px-3 py-3 text-center font-bold text-amber-800 bg-amber-50/40">S</th>
                            <th class="px-3 py-3 text-center font-bold text-blue-800 bg-blue-50/40">I</th>
                            <th class="px-3 py-3 text-center font-bold text-rose-800 bg-rose-50/40">A</th>
                            <th class="px-3 py-3 text-center font-bold text-violet-800 bg-violet-50/40">D</th>
                            <th class="px-3 py-3 text-center font-bold">Total JP</th>
                            <th class="px-4 py-3 text-right font-bold bg-slate-100/60">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($perMapel as $m)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-2.5 font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $m->warna }}"></span>
                                        <span>{{ $m->nama }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 text-center tabular-nums font-bold text-emerald-700 bg-emerald-50/20">{{ $m->hadir }}</td>
                                <td class="px-3 py-2.5 text-center tabular-nums text-amber-700 bg-amber-50/20">{{ $m->sakit }}</td>
                                <td class="px-3 py-2.5 text-center tabular-nums text-blue-700 bg-blue-50/20">{{ $m->izin }}</td>
                                <td class="px-3 py-2.5 text-center tabular-nums font-bold text-rose-700 bg-rose-50/20">{{ $m->alpa }}</td>
                                <td class="px-3 py-2.5 text-center tabular-nums text-violet-700 bg-violet-50/20">{{ $m->dispensasi }}</td>
                                <td class="px-3 py-2.5 text-center tabular-nums font-medium text-slate-500">{{ $m->total }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums font-extrabold bg-slate-50/40 {{ $m->persen < 75 ? 'text-rose-600' : 'text-slate-900' }}">{{ $m->persen }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</div>

<p class="mt-4 text-xs text-slate-400">
    Halaman ini dapat dicetak sebagai laporan resmi kehadiran siswa untuk wali murid. Gunakan tombol <strong>Cetak PDF A4</strong> di atas untuk mengunduh versi cetak.
</p>
@endif
@endsection
