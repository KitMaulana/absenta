<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\DailyAttendance;
use App\Models\Holiday;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Student;
use App\Models\SubjectAttendance;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DailyAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $tanggal = $this->tanggalDari($request);

        $siswa = Student::aktif()->urut()->get();
        $tersimpan = DailyAttendance::where('tanggal', $tanggal->toDateString())
            ->get()
            ->keyBy('student_id');

        $hari = Schedule::hariDari($tanggal);

        return view('admin.absensi-umum.index', [
            'tanggal' => $tanggal,
            'siswa' => $siswa,
            'tersimpan' => $tersimpan,
            'sudahDiinput' => $tersimpan->isNotEmpty(),
            'libur' => Holiday::whereDate('tanggal', $tanggal)->first(),
            'hariAktif' => in_array($hari, Setting::hariAktif(), true),
            'namaHari' => Schedule::HARI[$hari],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'array'],
            'status.*' => [Rule::in(AttendanceStatus::values())],
            'keterangan' => ['array'],
            'keterangan.*' => ['nullable', 'string', 'max:150'],
        ], [
            'tanggal.before_or_equal' => 'Absensi tidak bisa diisi untuk tanggal yang belum terjadi.',
        ]);

        $tanggal = CarbonImmutable::parse($data['tanggal'])->toDateString();

        if ($libur = Holiday::whereDate('tanggal', $tanggal)->first()) {
            return redirect()
                ->route('admin.absensi-umum.index', ['tanggal' => $tanggal])
                ->with('gagal', "Tanggal {$tanggal} terdaftar sebagai hari libur ({$libur->keterangan}). Presensi tidak dapat disimpan.");
        }

        $idSiswaAktif = Student::aktif()->pluck('id')->flip();
        $userId = $request->user()->id;
        $jumlah = 0;

        DB::transaction(function () use ($data, $tanggal, $idSiswaAktif, $userId, &$jumlah) {
            foreach ($data['status'] as $studentId => $status) {
                if (! $idSiswaAktif->has((int) $studentId)) {
                    continue;
                }

                DailyAttendance::updateOrCreate(
                    ['student_id' => (int) $studentId, 'tanggal' => $tanggal],
                    [
                        'status' => $status,
                        'keterangan' => $status === AttendanceStatus::Hadir->value
                            ? null
                            : ($data['keterangan'][$studentId] ?? null),
                        'created_by' => $userId,
                    ],
                );
                $jumlah++;
            }
        });

        return redirect()
            ->route('admin.absensi-umum.index', ['tanggal' => $tanggal])
            ->with('sukses', "Absensi umum {$tanggal} tersimpan untuk {$jumlah} siswa.");
    }

    public function setLibur(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'keterangan' => ['required', 'string', 'max:150'],
        ]);

        $tanggal = CarbonImmutable::parse($data['tanggal'])->toDateString();
        $keterangan = trim($data['keterangan']);

        DB::transaction(function () use ($tanggal, $keterangan) {
            Holiday::updateOrCreate(
                ['tanggal' => $tanggal],
                ['keterangan' => $keterangan]
            );

            // Bersihkan catatan presensi yang mungkin sempat tersimpan pada tanggal ini
            DailyAttendance::where('tanggal', $tanggal)->delete();
            SubjectAttendance::where('tanggal', $tanggal)->delete();
        });

        return redirect()
            ->route('admin.absensi-umum.index', ['tanggal' => $tanggal])
            ->with('sukses', "Tanggal {$tanggal} berhasil ditetapkan sebagai hari libur: {$keterangan}.");
    }

    public function batalLibur(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
        ]);

        $tanggal = CarbonImmutable::parse($data['tanggal'])->toDateString();

        Holiday::whereDate('tanggal', $tanggal)->delete();

        return redirect()
            ->route('admin.absensi-umum.index', ['tanggal' => $tanggal])
            ->with('sukses', "Status hari libur untuk tanggal {$tanggal} telah dibatalkan.");
    }

    private function tanggalDari(Request $request): CarbonImmutable
    {
        try {
            $tanggal = CarbonImmutable::parse($request->input('tanggal', 'today'));
        } catch (\Throwable) {
            $tanggal = CarbonImmutable::today();
        }

        return $tanggal->isFuture() ? CarbonImmutable::today() : $tanggal;
    }
}
