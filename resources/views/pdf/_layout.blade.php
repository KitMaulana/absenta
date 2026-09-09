{{-- Layout dasar semua laporan PDF. dompdf tidak mendukung flexbox/grid,
     jadi tata letak memakai tabel dan CSS sederhana. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $judul }} — {{ $pengaturan['nama_kelas'] }}</title>
    <style>
        @page { margin: 14mm 12mm 18mm 12mm; }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9.5px;
            color: #111827;
            margin: 0;
        }

        /* Kop laporan */
        .kop { width: 100%; border-bottom: 2px solid #111827; padding-bottom: 6px; margin-bottom: 10px; }
        .kop td { vertical-align: middle; }
        .kop .logo { width: 56px; }
        .kop .logo img { width: 48px; height: 48px; object-fit: contain; }
        .kop .sekolah { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .kop .kelas { font-size: 11px; }
        .kop .meta { font-size: 9px; color: #4b5563; }

        h1.judul { font-size: 13px; text-align: center; margin: 0 0 2px; text-transform: uppercase; }
        p.periode { font-size: 10px; text-align: center; margin: 0 0 10px; color: #374151; }

        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 0.5px solid #9ca3af; padding: 3px 4px; }
        table.data th { background: #f3f4f6; font-size: 8.5px; text-transform: uppercase; }
        table.data td.c { text-align: center; }
        table.data td.r { text-align: right; }
        table.data tbody tr:nth-child(even) { background: #fafafa; }

        .ringkas { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .ringkas td { border: 0.5px solid #d1d5db; padding: 5px 6px; text-align: center; width: 16.6%; }
        .ringkas .label { font-size: 8px; text-transform: uppercase; color: #6b7280; display: block; }
        .ringkas .nilai { font-size: 14px; font-weight: bold; }

        .ttd { width: 100%; margin-top: 22px; }
        .ttd td { width: 50%; vertical-align: top; font-size: 10px; }
        .ttd .ruang { height: 52px; }
        .ttd .nama { font-weight: bold; text-decoration: underline; }

        /* Footer berulang di tiap halaman */
        .footer {
            position: fixed; bottom: -10mm; left: 0; right: 0;
            font-size: 7.5px; color: #6b7280;
            border-top: 0.5px solid #d1d5db; padding-top: 3px;
        }
        .footer .kanan { float: right; }

        .catatan { font-size: 8px; color: #6b7280; margin-top: 6px; }
        .merah { color: #b91c1c; font-weight: bold; }
        .bar { height: 7px; background: #e5e7eb; }
        .bar span { display: block; height: 7px; }
    </style>
</head>
<body>

<div class="footer">
    Dicetak {{ \App\Support\Tanggal::panjang(now()) }} pukul {{ now()->format('H:i') }} oleh {{ $dicetakOleh }}
    <span class="kanan">{{ $pengaturan['nama_kelas'] }} — {{ $pengaturan['tahun_ajaran'] }} {{ $pengaturan['semester'] }}</span>
</div>

<table class="kop">
    <tr>
        @php $logo = $pengaturan['logo'] ?? ''; @endphp
        @if ($logo && file_exists(storage_path('app/public/'.$logo)))
            <td class="logo"><img src="{{ storage_path('app/public/'.$logo) }}" alt=""></td>
        @endif
        <td>
            <div class="sekolah">{{ $pengaturan['nama_sekolah'] ?: 'Laporan Absensi Kelas' }}</div>
            <div class="kelas">Kelas {{ $pengaturan['nama_kelas'] }} — Wali Kelas: {{ $pengaturan['nama_wali_kelas'] }}</div>
            <div class="meta">Tahun Ajaran {{ $pengaturan['tahun_ajaran'] }} &middot; Semester {{ $pengaturan['semester'] }}</div>
        </td>
    </tr>
</table>

<h1 class="judul">{{ $judul }}</h1>
<p class="periode">Periode: {{ $periode->label() }}</p>

@yield('isi')

</body>
</html>
