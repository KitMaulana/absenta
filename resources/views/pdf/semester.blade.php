@extends('pdf._layout')

@section('isi')
    <table class="ringkas">
        <tr>
            <td>
                <span class="label">Hari Efektif</span>
                <span class="nilai">{{ $jumlahHariEfektif }}</span>
            </td>
            @foreach (\App\Enums\AttendanceStatus::cases() as $status)
                <td>
                    <span class="label">{{ $status->label() }}</span>
                    <span class="nilai" style="color: {{ $status->warna() }}">{{ $ringkasan[$status->value] }}</span>
                </td>
            @endforeach
        </tr>
    </table>

    <p style="margin: 0 0 10px">
        Persentase kehadiran kelas pada semester ini: <strong>{{ $ringkasan['persen'] }}%</strong>
        dari total {{ number_format($ringkasan['total']) }} entri absensi harian.
    </p>

    <p style="font-weight: bold; margin: 12px 0 4px">A. Rekapitulasi Kehadiran Semester per Siswa</p>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 51%; text-align: left">Nama Siswa</th>
                <th style="width: 7%">H</th>
                <th style="width: 7%">S</th>
                <th style="width: 7%">I</th>
                <th style="width: 7%">A</th>
                <th style="width: 7%">D</th>
                <th style="width: 9%">% Hadir</th>
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
                    <td class="c {{ $b->persen < 75 ? 'merah' : '' }}">{{ $b->persen }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="font-weight: bold; margin: 14px 0 4px">B. Siswa yang Perlu Perhatian (alpa 3 kali atau lebih)</p>

    @if ($rawanAlpa->isEmpty())
        <p class="catatan">Tidak ada siswa dengan alpa 3 kali atau lebih pada semester ini.</p>
    @else
        <table class="data" style="width: 60%">
            <thead>
                <tr>
                    <th style="width: 8%">No</th>
                    <th style="width: 62%; text-align: left">Nama Siswa</th>
                    <th style="width: 30%">Jumlah Alpa</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rawanAlpa as $r)
                    <tr>
                        <td class="c">{{ $r->no_absen }}</td>
                        <td>{{ $r->nama }}</td>
                        <td class="c merah">{{ $r->alpa }} kali</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p style="font-weight: bold; margin: 14px 0 4px">C. Kehadiran per Mata Pelajaran (Semester)</p>

    @if ($perMapel->isEmpty())
        <p class="catatan">Belum ada data absensi mata pelajaran pada semester ini.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 35%; text-align: left">Mata Pelajaran</th>
                    <th style="width: 10%">Hadir</th>
                    <th style="width: 12%">Tot. JP</th>
                    <th style="width: 12%">% Hadir</th>
                    <th style="width: 31%">Grafik</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($perMapel as $m)
                    <tr>
                        <td>{{ $m->nama }}</td>
                        <td class="c">{{ $m->hadir }}</td>
                        <td class="c">{{ $m->total }}</td>
                        <td class="c">{{ $m->persen }}%</td>
                        <td>
                            <div class="bar"><span style="width: {{ $m->persen }}%; background: {{ $m->warna }}"></span></div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table class="ttd">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Sekolah,
                <div class="ruang"></div>
                <span class="nama">(............................................)</span>
            </td>
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
