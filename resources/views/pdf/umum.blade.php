@extends('pdf._layout')

@section('isi')
    @php
        $jumlahTanggal = max(1, count($hariEfektif));

        // Kolom tanggal menyempit otomatis saat rentangnya panjang.
        $fs = $jumlahTanggal > 24 ? '6.5px' : ($jumlahTanggal > 16 ? '7.5px' : '8.5px');

        // Lebar dinyatakan dalam persen — dompdf melebarkan baris secara berlebihan
        // bila diberi lebar piksel tetap yang tidak muat. Sisa lebar setelah kolom
        // No (4%), rekap H/S/I/A/D + % (18%), dan nama dibagi rata ke kolom tanggal.
        $lebarTanggal = round(min(2.2, max(0.9, 58 / $jumlahTanggal)), 2);
        $lebarNama = round(max(12, 78 - $lebarTanggal * $jumlahTanggal), 2);
    @endphp

    <table class="data" style="font-size: {{ $fs }}">
        <thead>
            <tr>
                <th style="width: 4%">No</th>
                <th style="width: {{ $lebarNama }}%; text-align: left">Nama Siswa</th>
                @foreach ($hariEfektif as $tgl)
                    <th style="width: {{ $lebarTanggal }}%">{{ \Carbon\CarbonImmutable::parse($tgl)->day }}</th>
                @endforeach
                <th style="width: 3%">H</th>
                <th style="width: 3%">S</th>
                <th style="width: 3%">I</th>
                <th style="width: 3%">A</th>
                <th style="width: 3%">D</th>
                <th style="width: 5%">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($baris as $b)
                <tr>
                    <td class="c">{{ $b->siswa->no_absen }}</td>
                    <td>{{ $b->siswa->nama }}</td>
                    @foreach ($hariEfektif as $tgl)
                        @php $kode = $matriks[$b->siswa->id][$tgl] ?? '-'; @endphp
                        <td class="c {{ $kode === 'A' ? 'merah' : '' }}">{{ $kode }}</td>
                    @endforeach
                    <td class="c">{{ $b->hitung['hadir'] }}</td>
                    <td class="c">{{ $b->hitung['sakit'] }}</td>
                    <td class="c">{{ $b->hitung['izin'] }}</td>
                    <td class="c {{ $b->hitung['alpa'] > 0 ? 'merah' : '' }}">{{ $b->hitung['alpa'] }}</td>
                    <td class="c">{{ $b->hitung['dispensasi'] }}</td>
                    <td class="c {{ $b->persen < 75 ? 'merah' : '' }}">{{ $b->persen }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="catatan">
        Keterangan kode: H = Hadir, S = Sakit, I = Izin, A = Alpa, D = Dispensasi, - = belum diinput.
        Hari efektif yang ditampilkan: {{ count($hariEfektif) }} hari (di luar hari libur dan hari non-aktif).
        Persentase = (hadir + dispensasi) / total entri siswa.
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
@endsection
