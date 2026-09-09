<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Subject;
use App\Services\RekapService;
use App\Support\Periode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RekapController extends Controller
{
    public const JENIS = [
        'umum' => 'Rekap Absensi Umum',
        'mapel' => 'Rekap per Mata Pelajaran',
        'siswa' => 'Rekap per Siswa',
        'bulanan' => 'Laporan Bulanan Wali Kelas',
        'infografis' => 'Infografis Versi Cetak',
    ];

    public function __construct(private readonly RekapService $rekap) {}

    public function show(Request $request, string $jenis): View
    {
        $this->pastikanJenisValid($jenis);

        return view("admin.rekap.{$jenis}", $this->data($request, $jenis) + [
            'jenis' => $jenis,
            'judul' => self::JENIS[$jenis],
        ]);
    }

    public function pdf(Request $request, string $jenis): Response
    {
        $this->pastikanJenisValid($jenis);

        $data = $this->data($request, $jenis);
        $periode = $data['periode'];

        // Matriks tanggal jadi sangat lebar; landscape begitu lebih dari sepekan.
        $landscape = match ($jenis) {
            'umum' => $periode->jumlahHari() > 7,
            'infografis' => false,
            default => false,
        };

        $pdf = Pdf::loadView("pdf.{$jenis}", $data + [
            'judul' => self::JENIS[$jenis],
            'dicetakOleh' => $request->user()->name,
            'landscape' => $landscape,
        ])->setPaper('a4', $landscape ? 'landscape' : 'portrait');

        $nama = sprintf('%s-%s.pdf', $jenis, $periode->mulai->format('Ymd'));

        return $pdf->stream($nama);
    }

    /** @return array<string,mixed> */
    private function data(Request $request, string $jenis): array
    {
        $periode = Periode::dariRequest($request);

        $dasar = [
            'periode' => $periode,
            'ringkasan' => $this->rekap->ringkasanPeriode($periode->mulai, $periode->selesai),
        ];

        return $dasar + match ($jenis) {
            'umum' => [
                'hariEfektif' => $this->rekap->hariEfektif($periode->mulai, $periode->selesai),
                'matriks' => $this->rekap->matriksHarian($periode->mulai, $periode->selesai),
                'baris' => $this->rekap->perSiswa($periode->mulai, $periode->selesai),
            ],
            'mapel' => $this->dataMapel($request, $periode),
            'siswa' => $this->dataSiswa($request, $periode),
            'bulanan' => [
                'baris' => $this->rekap->perSiswa($periode->mulai, $periode->selesai),
                'rawanAlpa' => $this->rekap->siswaRawanAlpa($periode->mulai, $periode->selesai),
                'perMapel' => $this->rekap->persenPerMapel($periode->mulai, $periode->selesai),
                'jumlahHariEfektif' => count($this->rekap->hariEfektif($periode->mulai, $periode->selesai)),
            ],
            'infografis' => [
                'tren' => $this->rekap->trenHarian($periode->mulai, $periode->selesai),
                'perMapel' => $this->rekap->persenPerMapel($periode->mulai, $periode->selesai),
                'baris' => $this->rekap->perSiswa($periode->mulai, $periode->selesai),
                'jumlahHariEfektif' => count($this->rekap->hariEfektif($periode->mulai, $periode->selesai)),
            ],
            default => [],
        };
    }

    private function dataMapel(Request $request, Periode $periode): array
    {
        $daftar = Subject::aktif()->orderBy('nama')->get();
        $terpilih = $daftar->firstWhere('id', (int) $request->input('mapel')) ?? $daftar->first();

        return [
            'daftarMapel' => $daftar,
            'mapel' => $terpilih,
            'baris' => $terpilih
                ? $this->rekap->perSiswaUntukMapel($terpilih->id, $periode->mulai, $periode->selesai)
                : collect(),
        ];
    }

    private function dataSiswa(Request $request, Periode $periode): array
    {
        $daftar = Student::aktif()->urut()->get();
        $terpilih = $daftar->firstWhere('id', (int) $request->input('siswa')) ?? $daftar->first();

        $rekapUmum = $terpilih
            ? $this->rekap->perSiswa($periode->mulai, $periode->selesai)->firstWhere('siswa.id', $terpilih->id)
            : null;

        return [
            'daftarSiswa' => $daftar,
            'siswa' => $terpilih,
            'umum' => $rekapUmum,
            'perMapel' => $terpilih
                ? $this->rekap->mapelUntukSiswa($terpilih->id, $periode->mulai, $periode->selesai)
                : collect(),
        ];
    }

    private function pastikanJenisValid(string $jenis): void
    {
        if (! isset(self::JENIS[$jenis])) {
            throw new NotFoundHttpException("Jenis rekap '{$jenis}' tidak dikenal.");
        }
    }
}
