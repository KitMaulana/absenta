@extends('layouts.admin')
@section('title', $judul)
@section('subtitle', $periode->label())

@section('content')
@include('admin.rekap._filter')

<x-card padat>
    <x-slot:judul>
        <div class="flex items-center gap-2">
            <span>Matriks Kehadiran Siswa &times; Tanggal</span>
            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">{{ count($hariEfektif) }} Hari Efektif</span>
        </div>
    </x-slot:judul>

    @if ($baris->isEmpty())
        <x-kosong pesan="Belum ada siswa aktif." ikon="🧑‍🎓" />
    @elseif ($hariEfektif === [])
        <x-kosong pesan="Tidak ada hari efektif pada periode ini (seluruh tanggal libur atau bukan hari aktif)." ikon="🏖️" />
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                    <tr class="bg-slate-50/90 text-slate-500 border-b border-slate-200/80">
                        <th class="sticky left-0 z-10 bg-slate-50 px-3 py-3 text-left font-bold w-10 text-center">No</th>
                        <th class="sticky left-10 z-10 bg-slate-50 px-3 py-3 text-left font-bold min-w-[10rem]">Nama Lengkap</th>
                        @foreach ($hariEfektif as $tgl)
                            @php $d = \Carbon\CarbonImmutable::parse($tgl); @endphp
                            <th class="w-7 px-1 py-3 text-center font-bold" title="{{ \App\Support\Tanggal::panjang($d) }}">
                                {{ $d->day }}
                            </th>
                        @endforeach
                        <th class="px-2 py-3 text-center font-bold text-emerald-800 bg-emerald-50/50">H</th>
                        <th class="px-2 py-3 text-center font-bold text-amber-800 bg-amber-50/50">S</th>
                        <th class="px-2 py-3 text-center font-bold text-blue-800 bg-blue-50/50">I</th>
                        <th class="px-2 py-3 text-center font-bold text-rose-800 bg-rose-50/50">A</th>
                        <th class="px-2 py-3 text-center font-bold text-violet-800 bg-violet-50/50">D</th>
                        <th class="px-3 py-3 text-center font-bold bg-slate-100/60">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($baris as $b)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="sticky left-0 z-10 bg-white px-3 py-2 text-center tabular-nums font-bold text-slate-400">{{ $b->siswa->no_absen }}</td>
                            <td class="sticky left-10 z-10 whitespace-nowrap bg-white px-3 py-2 font-bold text-slate-900 shadow-xs sm:shadow-none">{{ $b->siswa->nama }}</td>
                            @foreach ($hariEfektif as $tgl)
                                @php $kode = $matriks[$b->siswa->id][$tgl] ?? null; @endphp
                                <td class="px-1 py-2 text-center font-bold
                                    @class([
                                        'text-slate-200' => $kode === null,
                                        'text-slate-400' => $kode === 'H',
                                        'text-amber-600 bg-amber-50/30' => $kode === 'S',
                                        'text-blue-600 bg-blue-50/30' => $kode === 'I',
                                        'bg-rose-50 text-rose-600 font-black' => $kode === 'A',
                                        'text-violet-600 bg-violet-50/30' => $kode === 'D',
                                    ])">{{ $kode ?? '·' }}</td>
                            @endforeach
                            <td class="px-2 py-2 text-center tabular-nums font-bold text-emerald-700 bg-emerald-50/20">{{ $b->hitung['hadir'] }}</td>
                            <td class="px-2 py-2 text-center tabular-nums text-amber-700 bg-amber-50/20">{{ $b->hitung['sakit'] }}</td>
                            <td class="px-2 py-2 text-center tabular-nums text-blue-700 bg-blue-50/20">{{ $b->hitung['izin'] }}</td>
                            <td class="px-2 py-2 text-center tabular-nums font-bold text-rose-700 bg-rose-50/20">{{ $b->hitung['alpa'] }}</td>
                            <td class="px-2 py-2 text-center tabular-nums text-violet-700 bg-violet-50/20">{{ $b->hitung['dispensasi'] }}</td>
                            <td class="px-3 py-2 text-center tabular-nums font-extrabold bg-slate-50/40 {{ $b->persen < 75 ? 'text-rose-600' : 'text-slate-900' }}">{{ $b->persen }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 bg-slate-50/40 px-5 py-3 text-xs text-slate-500 rounded-b-2xl flex flex-wrap items-center justify-between gap-2">
            <div>
                <strong>Keterangan Status:</strong>
                <span class="ml-1 text-slate-700"><strong>H</strong>: Hadir &middot; <strong>S</strong>: Sakit &middot; <strong>I</strong>: Izin &middot; <strong>A</strong>: Alpa &middot; <strong>D</strong>: Dispensasi</span>
            </div>
            <p class="text-slate-400">
                *Persentase dihitung dari akumulasi Hadir + Dispensasi terhadap total entri tercatat.
            </p>
        </div>
    @endif
</x-card>
@endsection
