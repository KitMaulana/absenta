@extends('pdf._layout')

@section('isi')
    @if (! $mapel)
        <p>Belum ada mata pelajaran aktif.</p>
    @else
        <p class="periode" style="margin-bottom: 8px">
            Mata Pelajaran: <strong>{{ $mapel->nama }}</strong> ({{ $mapel->singkatan }})
        </p>

        {{-- Lebar kolom memakai persen: dompdf melebarkan baris secara berlebihan
             bila diberi lebar piksel tetap yang tidak muat. --}}
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 41%; text-align: left">Nama Siswa</th>
                    <th style="width: 7%">Hadir</th>
                    <th style="width: 7%">Sakit</th>
                    <th style="width: 7%">Izin</th>
                    <th style="width: 7%">Alpa</th>
                    <th style="width: 7%">Disp.</th>
                    <th style="width: 9%">Tot. JP</th>
                    <th style="width: 10%">% Hadir</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($baris as $b)
                    <tr>
                        <td class="c">{{ $b->siswa->no_absen }}</td>
                        <td>{{ $b->siswa->nama }}</td>
                        <td class="c">{{ $b->hitung['hadir'] }}</td>
                        <td class="c">{{ $b->hitung['sakit'] }}</td>
                        <td class="c">{{ $b->hitung['izin'] }}</td>
                        <td class="c {{ $b->hitung['alpa'] > 0 ? 'merah' : '' }}">{{ $b->hitung['alpa'] }}</td>
                        <td class="c">{{ $b->hitung['dispensasi'] }}</td>
                        <td class="c">{{ $b->total }}</td>
                        <td class="c {{ $b->persen < 75 ? 'merah' : '' }}">{{ $b->persen }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p class="catatan">
            Angka dihitung per jam pelajaran, bukan per hari. Satu mata pelajaran yang diampu 2 JP dalam sehari
            menyumbang 2 entri. Persentase = (hadir + dispensasi) / total JP siswa tersebut.
        </p>

        <table class="ttd">
            <tr>
                <td></td>
                <td>
                    {{ \App\Support\Tanggal::pendek(now()) }}<br>
                    Wali Kelas {{ $pengaturan['nama_kelas'] }},
                    <div class="ruang"></div>
                    <span class="nama">{{ $pengaturan['nama_wali_kelas'] }}</span>
                    @if (! empty($pengaturan['nip_wali_kelas']))
                        <br>NIP. {{ $pengaturan['nip_wali_kelas'] }}
                    @endif
                </td>
            </tr>
        </table>
    @endif
@endsection
