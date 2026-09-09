@extends('pdf._layout')

@section('isi')
    @if (! $siswa)
        <p>Belum ada siswa aktif.</p>
    @else
        <table class="data" style="margin-bottom: 10px">
            <tr>
                <th style="width: 18%; text-align: left">Nama Siswa</th>
                <td style="width: 32%">{{ $siswa->nama }}</td>
                <th style="width: 18%; text-align: left">Nomor Absen</th>
                <td style="width: 32%">{{ $siswa->no_absen }}</td>
            </tr>
            <tr>
                <th style="text-align: left">Kelas</th>
                <td>{{ $pengaturan['nama_kelas'] }}</td>
                <th style="text-align: left">Jenis Kelamin</th>
                <td>{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
        </table>

        <p style="font-weight: bold; margin: 12px 0 4px">A. Rekapitulasi Absensi Umum Harian</p>

        @if ($umum)
            <table class="ringkas">
                <tr>
                    @foreach (\App\Enums\AttendanceStatus::cases() as $status)
                        <td>
                            <span class="label">{{ $status->label() }}</span>
                            <span class="nilai" style="color: {{ $status->warna() }}">{{ $umum->hitung[$status->value] }}</span>
                        </td>
                    @endforeach
                    <td>
                        <span class="label">% Kehadiran</span>
                        <span class="nilai">{{ $umum->persen }}%</span>
                    </td>
                </tr>
            </table>
            <p class="catatan" style="margin-top: 0">Dari total {{ $umum->total }} hari yang tercatat pada periode ini.</p>
        @else
            <p class="catatan">Belum ada data absensi umum pada periode ini.</p>
        @endif

        <p style="font-weight: bold; margin: 14px 0 4px">B. Rekapitulasi Kehadiran per Mata Pelajaran</p>

        @if ($perMapel->isEmpty())
            <p class="catatan">Belum ada data absensi mata pelajaran pada periode ini.</p>
        @else
            <table class="data">
                <thead>
                    <tr>
                        <th style="width: 43%; text-align: left">Mata Pelajaran</th>
                        <th style="width: 7%">H</th>
                        <th style="width: 7%">S</th>
                        <th style="width: 7%">I</th>
                        <th style="width: 7%">A</th>
                        <th style="width: 7%">D</th>
                        <th style="width: 10%">Total</th>
                        <th style="width: 12%">% Hadir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($perMapel as $m)
                        <tr>
                            <td>{{ $m->nama }}</td>
                            <td class="c">{{ $m->hadir }}</td>
                            <td class="c">{{ $m->sakit }}</td>
                            <td class="c">{{ $m->izin }}</td>
                            <td class="c {{ $m->alpa > 0 ? 'merah' : '' }}">{{ $m->alpa }}</td>
                            <td class="c">{{ $m->dispensasi }}</td>
                            <td class="c">{{ $m->total }}</td>
                            <td class="c {{ $m->persen < 75 ? 'merah' : '' }}">{{ $m->persen }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <p class="catatan">
            Laporan ini dibuat untuk disampaikan kepada orang tua/wali murid.
            Kehadiran di bawah 75% ditandai dengan warna merah.
        </p>

        <table class="ttd">
            <tr>
                <td>
                    Mengetahui,<br>
                    Orang Tua/Wali Murid,
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
    @endif
@endsection
