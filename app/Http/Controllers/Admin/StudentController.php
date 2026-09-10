<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\DailyAttendance;
use App\Models\Student;
use App\Models\SubjectAttendance;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function resetTotal(Request $request): RedirectResponse
    {
        $request->validate([
            'konfirmasi' => ['required', 'string', 'in:HAPUS,hapus,RESET,reset'],
            'hapus_absensi' => ['nullable', 'boolean'],
        ], [
            'konfirmasi.in' => 'Ketik kata HAPUS untuk mengonfirmasi reset data siswa.',
        ]);

        $jumlahSiswa = Student::count();

        if ($jumlahSiswa === 0) {
            return back()->with('gagal', 'Tidak ada data siswa untuk direset.');
        }

        DB::transaction(function () use ($request) {
            if ($request->boolean('hapus_absensi', true)) {
                DailyAttendance::query()->delete();
                SubjectAttendance::query()->delete();
            }

            Student::query()->delete();
        });

        return redirect()->route('admin.siswa.index')
            ->with('sukses', "Berhasil mereset total {$jumlahSiswa} data siswa. Seluruh data siswa kini telah bersih.");
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

        $rawLines = file($request->file('file')->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($rawLines === false || $rawLines === []) {
            return back()->with('gagal', 'Berkas CSV kosong.');
        }

        // Deteksi delimiter otomatis (koma atau titik koma)
        $firstLine = $rawLines[0];
        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        $baris = array_map(fn ($line) => str_getcsv($line, $delimiter), $rawLines);

        $header = array_map(fn ($h) => strtolower(trim($h, " \t\n\r\0\x0B\xEF\xBB\xBF")), array_shift($baris));
        $wajib = ['no_absen', 'nama', 'jenis_kelamin'];

        if (array_diff($wajib, $header)) {
            return back()->with('gagal', 'Header CSV wajib memuat kolom: '.implode(', ', $wajib).'.');
        }

        $kolom = array_flip($header);
        $ambil = fn (array $r, string $k) => isset($kolom[$k]) ? trim($r[$kolom[$k]] ?? '') : null;

        $masuk = 0;
        $dilewati = [];
        $nisnTerproses = [];

        try {
            DB::transaction(function () use ($request, $baris, $ambil, &$masuk, &$dilewati, &$nisnTerproses) {
                if ($request->input('mode') === 'ganti') {
                    Student::whereDoesntHave('dailyAttendances')->whereDoesntHave('subjectAttendances')->delete();
                    Student::query()->update(['is_active' => false]);
                }

                foreach ($baris as $i => $row) {
                    $barisKe = $i + 2;

                    // Lewati jika seluruh kolom dalam baris kosong
                    if (empty(array_filter($row, fn ($v) => $v !== null && trim((string) $v) !== ''))) {
                        continue;
                    }

                    $nama = trim((string) $ambil($row, 'nama'));
                    $nama = preg_replace('/\s+/', ' ', $nama);

                    $jkRaw = strtoupper(trim((string) $ambil($row, 'jenis_kelamin')));
                    $jk = match ($jkRaw) {
                        'L', 'LAKI-LAKI', 'LAKILAKI', 'PRIA' => 'L',
                        'P', 'PEREMPUAN', 'WANITA' => 'P',
                        default => $jkRaw,
                    };

                    $noAbsen = (int) trim((string) $ambil($row, 'no_absen'));

                    if ($nama === '' || ! in_array($jk, ['L', 'P'], true) || $noAbsen < 1) {
                        $dilewati[] = "baris {$barisKe} (data tidak lengkap)";

                        continue;
                    }

                    // Sanitasi NISN (hapus petik dari format teks Excel dan trim spasi)
                    $nisnRaw = $ambil($row, 'nisn');
                    $nisn = null;
                    if ($nisnRaw !== null) {
                        $nisnClean = ltrim(trim($nisnRaw), "'");
                        $nisnClean = trim($nisnClean);
                        if ($nisnClean !== '') {
                            $nisn = $nisnClean;
                        }
                    }

                    // Deteksi duplikasi NISN di dalam file CSV itu sendiri
                    if ($nisn !== null && isset($nisnTerproses[$nisn])) {
                        $dilewati[] = "baris {$barisKe} (NISN {$nisn} duplikat di file CSV)";

                        continue;
                    }

                    // Sanitasi nomor HP ortu
                    $noHpRaw = $ambil($row, 'no_hp_ortu');
                    $noHp = null;
                    if ($noHpRaw !== null) {
                        $noHpClean = ltrim(trim($noHpRaw), "'");
                        $noHpClean = trim($noHpClean);
                        if ($noHpClean !== '') {
                            $noHp = $noHpClean;
                        }
                    }

                    // 1. Identifikasi siswa berdasarkan NISN jika tersedia
                    $siswa = null;
                    if ($nisn !== null) {
                        $siswa = Student::where('nisn', $nisn)->first();
                    }

                    // 2. Jika tidak ditemukan lewat NISN, cocokkan berdasarkan nama (case-insensitive)
                    if ($siswa === null) {
                        $siswa = Student::where('nama', $nama)
                            ->orWhereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($nama)])
                            ->first();
                    }

                    try {
                        if ($siswa !== null) {
                            $siswa->update([
                                'nama' => $nama,
                                'no_absen' => $noAbsen,
                                'nisn' => $nisn ?? $siswa->nisn,
                                'jenis_kelamin' => $jk,
                                'no_hp_ortu' => $noHp ?? $siswa->no_hp_ortu,
                                'is_active' => true,
                            ]);
                        } else {
                            Student::create([
                                'nama' => $nama,
                                'no_absen' => $noAbsen,
                                'nisn' => $nisn,
                                'jenis_kelamin' => $jk,
                                'no_hp_ortu' => $noHp,
                                'is_active' => true,
                            ]);
                        }

                        if ($nisn !== null) {
                            $nisnTerproses[$nisn] = $barisKe;
                        }

                        $masuk++;
                    } catch (UniqueConstraintViolationException $e) {
                        $dilewati[] = "baris {$barisKe} (duplikat NISN: ".($nisn ?? '-').')';
                    }
                }
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->with('gagal', 'Gagal memproses berkas CSV: '.$e->getMessage());
        }

        $pesan = "Impor selesai: {$masuk} siswa diproses.";

        if ($dilewati !== []) {
            $pesan .= ' Dilewati: '.implode(', ', array_slice($dilewati, 0, 10)).(count($dilewati) > 10 ? ' dan '.(count($dilewati) - 10).' lainnya' : '').'.';
        }

        return redirect()->route('admin.siswa.index')->with('sukses', $pesan);
    }
}
