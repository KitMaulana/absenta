<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleRequest;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ScheduleController extends Controller
{
    public function index(): View
    {
        return view('admin.jadwal.index', [
            'perHari' => Schedule::with('subject')->orderBy('jam_ke')->get()->groupBy('hari'),
            'hariAktif' => Setting::hariAktif(),
        ]);
    }

    public function create(): View
    {
        return view('admin.jadwal.form', [
            'item' => new Schedule(['hari' => 'senin']),
            'mapel' => Subject::aktif()->orderBy('nama')->get(),
        ]);
    }

    public function store(ScheduleRequest $request): RedirectResponse
    {
        Schedule::create($request->validated());

        return redirect()->route('admin.jadwal.index')->with('sukses', 'Jadwal berhasil ditambahkan.');
    }

    public function edit(Schedule $jadwal): View
    {
        return view('admin.jadwal.form', [
            'item' => $jadwal,
            'mapel' => Subject::aktif()->orderBy('nama')->get(),
        ]);
    }

    public function update(ScheduleRequest $request, Schedule $jadwal): RedirectResponse
    {
        $jadwal->update($request->validated());

        return redirect()->route('admin.jadwal.index')->with('sukses', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Schedule $jadwal): RedirectResponse
    {
        if ($jadwal->subjectAttendances()->exists()) {
            return back()->with('gagal', 'Jadwal ini sudah punya riwayat absensi dan tidak bisa dihapus.');
        }

        $jadwal->delete();

        return back()->with('sukses', 'Jadwal berhasil dihapus.');
    }

    /**
     * Download template CSV jadwal pelajaran
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        $path = public_path('Template_Jadwal.csv');

        return response()->download($path, 'Template_Jadwal.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Tampilan form upload CSV import jadwal
     */
    public function showImport(): View
    {
        return view('admin.jadwal.import');
    }

    /**
     * Proses import jadwal dari file CSV
     */
    public function importCsv(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'mode' => ['required', Rule::in(['tambah', 'ganti'])],
        ], [], ['file' => 'Berkas CSV']);

        $rawLines = file($request->file('file')->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($rawLines === false || empty($rawLines)) {
            return back()->with('gagal', 'Berkas CSV kosong.');
        }

        // Deteksi delimiter otomatis
        $firstLine = $rawLines[0];
        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        // Bersihkan header dari UTF-8 BOM
        $header = str_getcsv(str_replace("\xEF\xBB\xBF", '', $rawLines[0]), $delimiter);
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);

        $required = ['hari', 'jam_ke', 'mata_pelajaran'];
        foreach ($required as $col) {
            if (! in_array($col, $header, true)) {
                return back()->with('gagal', "Kolom wajib '{$col}' tidak ditemukan di berkas CSV. Kolom wajib: " . implode(', ', $required));
            }
        }

        $mode = $request->input('mode');
        $validHari = array_keys(Schedule::HARI);
        $imported = 0;
        $errors = [];
        $lineNum = 1;

        DB::transaction(function () use ($rawLines, $delimiter, $header, $mode, $validHari, &$imported, &$errors, &$lineNum) {
            if ($mode === 'ganti') {
                // Hapus jadwal yang belum ada riwayat absensi
                Schedule::doesntHave('subjectAttendances')->delete();
            }

            for ($i = 1; $i < count($rawLines); $i++) {
                $lineNum = $i + 1;
                $row = str_getcsv($rawLines[$i], $delimiter);

                if (count($row) < 3 || empty(array_filter($row, fn ($v) => trim((string) $v) !== ''))) {
                    continue;
                }

                $data = array_combine($header, array_pad($row, count($header), ''));

                $hariRaw = strtolower(trim((string) ($data['hari'] ?? '')));
                if (! in_array($hariRaw, $validHari, true)) {
                    $errors[] = "Baris {$lineNum}: Hari '{$data['hari']}' tidak valid (Gunakan: Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Minggu).";
                    continue;
                }

                $jamKe = (int) ($data['jam_ke'] ?? 0);
                if ($jamKe < 1 || $jamKe > 15) {
                    $errors[] = "Baris {$lineNum}: Jam ke-{$data['jam_ke']} tidak valid (harus angka 1–15).";
                    continue;
                }

                $mapelNama = trim((string) ($data['mata_pelajaran'] ?? ''));
                if (empty($mapelNama)) {
                    $errors[] = "Baris {$lineNum}: Nama mata pelajaran tidak boleh kosong.";
                    continue;
                }

                // Cari atau buat mata pelajaran
                $subject = Subject::whereRaw('LOWER(nama) = ?', [strtolower($mapelNama)])
                    ->orWhereRaw('LOWER(singkatan) = ?', [strtolower($mapelNama)])
                    ->first();

                if (! $subject) {
                    $subject = $this->buatMapelBaru($mapelNama);
                }

                $guru = trim((string) ($data['guru_pengampu'] ?? '')) ?: null;
                $jamMulai = $this->formatJam($data['jam_mulai'] ?? null);
                $jamSelesai = $this->formatJam($data['jam_selesai'] ?? null);

                Schedule::updateOrCreate(
                    [
                        'hari' => $hariRaw,
                        'jam_ke' => $jamKe,
                    ],
                    [
                        'subject_id' => $subject->id,
                        'guru_pengampu' => $guru,
                        'jam_mulai' => $jamMulai,
                        'jam_selesai' => $jamSelesai,
                    ]
                );

                $imported++;
            }
        });

        $pesan = "{$imported} jadwal pelajaran berhasil diimpor.";
        if (! empty($errors)) {
            $pesan .= ' ' . count($errors) . ' baris dilewati: ' . implode(' | ', array_slice($errors, 0, 5));
        }

        return redirect()->route('admin.jadwal.index')->with('sukses', $pesan);
    }

    /**
     * Tampilan form input massal rentang JP di web
     */
    public function bulkCreate(): View
    {
        return view('admin.jadwal.bulk-form', [
            'mapel' => Subject::aktif()->orderBy('nama')->get(),
            'hariList' => Schedule::HARI,
            'hariAktif' => Setting::hariAktif(),
        ]);
    }

    /**
     * Proses simpan massal rentang JP dari form web
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hari' => ['required', Rule::in(array_keys(Schedule::HARI))],
            'subject_id' => ['required', 'exists:subjects,id'],
            'guru_pengampu' => ['nullable', 'string', 'max:100'],
            'jam_ke_mulai' => ['required', 'integer', 'min:1', 'max:15'],
            'jam_ke_selesai' => ['required', 'integer', 'min:1', 'max:15', 'gte:jam_ke_mulai'],
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
        ], [
            'jam_ke_selesai.gte' => 'Jam selesai harus lebih besar atau sama dengan jam mulai.',
            'jam_selesai.after' => 'Waktu selesai harus lebih lambat dari waktu mulai.',
        ]);

        $hari = $data['hari'];
        $subjectId = (int) $data['subject_id'];
        $guru = $data['guru_pengampu'] ?? null;
        $mulai = (int) $data['jam_ke_mulai'];
        $selesai = (int) $data['jam_ke_selesai'];
        $jamMulai = $data['jam_mulai'] ?? null;
        $jamSelesai = $data['jam_selesai'] ?? null;

        $total = 0;
        DB::transaction(function () use ($hari, $subjectId, $guru, $mulai, $selesai, $jamMulai, $jamSelesai, &$total) {
            for ($jp = $mulai; $jp <= $selesai; $jp++) {
                Schedule::updateOrCreate(
                    [
                        'hari' => $hari,
                        'jam_ke' => $jp,
                    ],
                    [
                        'subject_id' => $subjectId,
                        'guru_pengampu' => $guru,
                        'jam_mulai' => $jamMulai,
                        'jam_selesai' => $jamSelesai,
                    ]
                );
                $total++;
            }
        });

        $subject = Subject::find($subjectId);
        $namaHari = Schedule::HARI[$hari];

        return redirect()->route('admin.jadwal.index')->with(
            'sukses',
            "Berhasil menyimpan {$total} JP ({$namaHari}, JP {$mulai}–{$selesai}) untuk mata pelajaran {$subject?->nama}."
        );
    }

    /**
     * Hapus massal jadwal yang dipilih
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || ! is_array($ids)) {
            return back()->with('gagal', 'Tidak ada jadwal pelajaran yang dipilih.');
        }

        $berhasil = 0;
        $terkunci = 0;

        foreach ($ids as $id) {
            $jadwal = Schedule::find($id);
            if (! $jadwal) {
                continue;
            }

            if ($jadwal->subjectAttendances()->exists()) {
                $terkunci++;
                continue;
            }

            $jadwal->delete();
            $berhasil++;
        }

        $pesan = "{$berhasil} jadwal pelajaran berhasil dihapus.";
        if ($terkunci > 0) {
            $pesan .= " {$terkunci} jadwal tidak dapat dihapus karena sudah memiliki riwayat absensi.";
        }

        return redirect()->route('admin.jadwal.index')->with('sukses', $pesan);
    }

    private function buatMapelBaru(string $nama): Subject
    {
        $singkatan = strtoupper(substr((string) preg_replace('/[^A-Za-z0-9]/', '', $nama), 0, 4));
        if (empty($singkatan)) {
            $singkatan = 'MPL';
        }

        $counter = 1;
        $baseSingkatan = $singkatan;
        while (Subject::where('singkatan', $singkatan)->exists()) {
            $singkatan = substr($baseSingkatan, 0, 3) . $counter++;
        }

        $palet = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#14b8a6', '#6366f1', '#84cc16'];
        $warna = $palet[abs(crc32($nama)) % count($palet)];

        return Subject::create([
            'nama' => $nama,
            'singkatan' => $singkatan,
            'warna' => $warna,
            'is_active' => true,
        ]);
    }

    private function formatJam(?string $val): ?string
    {
        if (! $val) {
            return null;
        }

        $val = trim($val);
        if (empty($val)) {
            return null;
        }

        if (preg_match('/^(\d{1,2})[:.](\d{2})/', $val, $m)) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }

        return null;
    }
}
