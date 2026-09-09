<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $siswa = Student::query()
            ->when($request->filled('cari'), function ($q) use ($request) {
                $cari = '%'.$request->string('cari').'%';
                $q->where(fn ($w) => $w->where('nama', 'like', $cari)->orWhere('nisn', 'like', $cari));
            })
            ->when($request->input('status') === 'nonaktif', fn ($q) => $q->where('is_active', false))
            ->when($request->input('status') !== 'semua' && $request->input('status') !== 'nonaktif',
                fn ($q) => $q->where('is_active', true))
            ->urut()
            ->paginate(25)
            ->withQueryString();

        return view('admin.siswa.index', [
            'siswa' => $siswa,
            'jumlahAktif' => Student::aktif()->count(),
            'jumlahNonaktif' => Student::where('is_active', false)->count(),
        ]);
    }

    public function create(): View
    {
        $berikutnya = (int) Student::max('no_absen') + 1;

        return view('admin.siswa.form', [
            'siswa' => new Student(['no_absen' => $berikutnya, 'jenis_kelamin' => 'L', 'is_active' => true]),
        ]);
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        Student::create($request->validated());

        return redirect()->route('admin.siswa.index')->with('sukses', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Student $siswa): View
    {
        return view('admin.siswa.form', compact('siswa'));
    }

    public function update(StudentRequest $request, Student $siswa): RedirectResponse
    {
        $siswa->update($request->validated());

        return redirect()->route('admin.siswa.index')->with('sukses', 'Data siswa berhasil diperbarui.');
    }

    /** Siswa pindah/keluar dinonaktifkan, bukan dihapus, agar riwayat absensi tetap utuh. */
    public function toggle(Student $siswa): RedirectResponse
    {
        $siswa->update(['is_active' => ! $siswa->is_active]);

        return back()->with('sukses', sprintf(
            '%s berhasil %s.', $siswa->nama, $siswa->is_active ? 'diaktifkan kembali' : 'dinonaktifkan',
        ));
    }

    public function destroy(Student $siswa): RedirectResponse
    {
        if ($siswa->dailyAttendances()->exists() || $siswa->subjectAttendances()->exists()) {
            return back()->with('gagal', 'Siswa ini sudah punya riwayat absensi. Nonaktifkan saja agar riwayat tidak hilang.');
        }

        $siswa->delete();

        return back()->with('sukses', 'Siswa berhasil dihapus.');
    }

    public function exportCsv(): StreamedResponse
    {
        $nama = 'siswa-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8 dengan benar
            fputcsv($out, ['no_absen', 'nama', 'nisn', 'jenis_kelamin', 'no_hp_ortu', 'is_active']);

            Student::urut()->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $s) {
                    fputcsv($out, [$s->no_absen, $s->nama, $s->nisn, $s->jenis_kelamin, $s->no_hp_ortu, $s->is_active ? 1 : 0]);
                }
            });

            fclose($out);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importForm(): View
    {
        return view('admin.siswa.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'mode' => ['required', Rule::in(['tambah', 'ganti'])],
        ], [], ['file' => 'berkas CSV']);

        $baris = array_map('str_getcsv', file($request->file('file')->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));

        if ($baris === []) {
            return back()->with('gagal', 'Berkas CSV kosong.');
        }

        $header = array_map(fn ($h) => strtolower(trim($h, " \t\n\r\0\x0B\xEF\xBB\xBF")), array_shift($baris));
        $wajib = ['no_absen', 'nama', 'jenis_kelamin'];

        if (array_diff($wajib, $header)) {
            return back()->with('gagal', 'Header CSV wajib memuat kolom: '.implode(', ', $wajib).'.');
        }

        $kolom = array_flip($header);
        $ambil = fn (array $r, string $k) => isset($kolom[$k]) ? trim($r[$kolom[$k]] ?? '') : null;

        $masuk = 0;
        $dilewati = [];

        if ($request->input('mode') === 'ganti') {
            Student::whereDoesntHave('dailyAttendances')->whereDoesntHave('subjectAttendances')->delete();
            Student::query()->update(['is_active' => false]);
        }

        foreach ($baris as $i => $row) {
            $nama = $ambil($row, 'nama');
            $jk = strtoupper((string) $ambil($row, 'jenis_kelamin'));
            $noAbsen = (int) $ambil($row, 'no_absen');

            if ($nama === '' || $nama === null || ! in_array($jk, ['L', 'P'], true) || $noAbsen < 1) {
                $dilewati[] = 'baris '.($i + 2);

                continue;
            }

            $nisn = $ambil($row, 'nisn');

            Student::updateOrCreate(
                ['nama' => $nama],
                [
                    'no_absen' => $noAbsen,
                    'nisn' => $nisn !== '' ? $nisn : null,
                    'jenis_kelamin' => $jk,
                    'no_hp_ortu' => $ambil($row, 'no_hp_ortu') ?: null,
                    'is_active' => true,
                ],
            );
            $masuk++;
        }

        $pesan = "Impor selesai: {$masuk} siswa diproses.";

        if ($dilewati !== []) {
            $pesan .= ' Dilewati karena data tidak lengkap: '.implode(', ', array_slice($dilewati, 0, 10)).'.';
        }

        return redirect()->route('admin.siswa.index')->with('sukses', $pesan);
    }
}
