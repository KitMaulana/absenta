@extends('pdf._layout')

@section('isi')
    @php
        // dompdf tidak menjalankan JavaScript, jadi semua "grafik" digambar
        // sebagai batang CSS. Skala tren dibuat relatif agar tetap terbaca.
        $statuses = \App\Enums\AttendanceStatus::cases();
        $totalKomposisi = max(1, $ringkasan['total']);
    @endphp

    <table class="ringkas">
        <tr>
            <td>
                <span class="label">Hari Efektif</span>
                <span class="nilai">{{ $jumlahHariEfektif }}</span>
            </td>
            @foreach ($statuses as $status)
                <td>
                    <span class="label">{{ $status->label() }}</span>
                    <span class="nilai" style="color: {{ $status->warna() }}">{{ $ringkasan[$status->value] }}</span>
                </td>
            @endforeach
        </tr>
    </table>

    <p style="font-weight: bold; margin: 12px 0 4px">A. Komposisi Kehadiran</p>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 25%; text-align: left">Status</th>
                <th style="width: 12%">Jumlah</th>
                <th style="width: 12%">Porsi</th>
                <th style="width: 51%">Grafik</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($statuses as $status)
                @php $porsi = round($ringkasan[$status->value] / $totalKomposisi * 100, 1); @endphp
                <tr>
                    <td>{{ $status->label() }}</td>
                    <td class="c">{{ $ringkasan[$status->value] }}</td>
                    <td class="c">{{ $porsi }}%</td>
                    <td><div class="bar"><span style="width: {{ $porsi }}%; background: {{ $status->warna() }}"></span></div></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="font-weight: bold; margin: 14px 0 4px">B. Kehadiran per Mata Pelajaran</p>

    @if ($perMapel->isEmpty())
        <p class="catatan">Belum ada data absensi mata pelajaran pada periode ini.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 28%; text-align: left">Mata Pelajaran</th>
                    <th style="width: 10%">Hadir</th>
                    <th style="width: 12%">Tot. JP</th>
                    <th style="width: 12%">% Hadir</th>
                    <th style="width: 38%">Grafik</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($perMapel as $m)
                    <tr>
                        <td>{{ $m->nama }}</td>
                        <td class="c">{{ $m->hadir }}</td>
                        <td class="c">{{ $m->total }}</td>
                        <td class="c">{{ $m->persen }}%</td>
                        <td><div class="bar"><span style="width: {{ $m->persen }}%; background: {{ $m->warna }}"></span></div></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p style="font-weight: bold; margin: 14px 0 4px">C. Tren Kehadiran Harian</p>

    @if (empty($tren['labels']))
        <p class="catatan">Belum ada data absensi harian pada periode ini.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 18%; text-align: left">Tanggal</th>
                    <th style="width: 12%">% Hadir</th>
                    <th style="width: 70%">Grafik</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tren['labels'] as $i => $label)
                    @php $nilai = $tren['persen'][$i]; @endphp
                    <tr>
                        <td>{{ $label }}</td>
                        <td class="c {{ $nilai < 75 ? 'merah' : '' }}">{{ $nilai }}%</td>
                        <td><div class="bar"><span style="width: {{ $nilai }}%; background: #4f46e5"></span></div></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="catatan">
        Grafik pada PDF digambar sebagai batang statis karena mesin PDF tidak menjalankan JavaScript.
        Untuk versi berwarna dengan diagram donat dan garis, gunakan tombol
        &ldquo;Print halaman ini&rdquo; pada halaman Infografis Cetak di aplikasi.
    </p>
@endsection
