<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\DailyAttendance;
use App\Models\Holiday;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\SubjectAttendance;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $tanggal = $this->tanggalDari($request);
        $hari = Schedule::hariDari($tanggal);

        $jadwal = Schedule::with('subject')->where('hari', $hari)->orderBy('jam_ke')->get();
        $siswa = Student::aktif()->urut()->get();

        // [schedule_id => [student_id => SubjectAttendance]]
        $tersimpan = SubjectAttendance::where('tanggal', $tanggal->toDateString())
            ->get()
            ->groupBy('schedule_id')
            ->map(fn ($rows) => $rows->keyBy('student_id'));

        // Status absensi umum hari itu dipakai sebagai nilai default tiap JP.
        $umum = DailyAttendance::where('tanggal', $tanggal->toDateString())
            ->pluck('status', 'student_id')
            ->map(fn ($s) => $s instanceof AttendanceStatus ? $s->value : $s);

        return view('admin.absensi-mapel.index', [
            'tanggal' => $tanggal,
            'namaHari' => Schedule::HARI[$hari],
            'jadwal' => $jadwal,
            'siswa' => $siswa,
            'tersimpan' => $tersimpan,
            'umum' => $umum,
            'umumAda' => $umum->isNotEmpty(),
            'libur' => Holiday::whereDate('tanggal', $tanggal)->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'schedule_id' => ['required', 'exists:schedules,id'],
            'status' => ['required', 'array'],
            'status.*' => [Rule::in(AttendanceStatus::values())],
            'keterangan' => ['array'],
            'keterangan.*' => ['nullable', 'string', 'max:150'],
        ], [
            'tanggal.before_or_equal' => 'Absensi tidak bisa diisi untuk tanggal yang belum terjadi.',
        ]);

        $tanggal = CarbonImmutable::parse($data['tanggal'])->toDateString();
        $scheduleId = (int) $data['schedule_id'];
        $idSiswaAktif = Student::aktif()->pluck('id')->flip();
        $userId = $request->user()->id;
        $jumlah = 0;

        DB::transaction(function () use ($data, $tanggal, $scheduleId, $idSiswaAktif, $userId, &$jumlah) {
            foreach ($data['status'] as $studentId => $status) {
                if (! $idSiswaAktif->has((int) $studentId)) {
                    continue;
                }

                SubjectAttendance::updateOrCreate(
                    ['student_id' => (int) $studentId, 'tanggal' => $tanggal, 'schedule_id' => $scheduleId],
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

        $jadwal = Schedule::with('subject')->find($scheduleId);

        return redirect()
            ->route('admin.absensi-mapel.index', ['tanggal' => $tanggal, 'jp' => $jadwal?->jam_ke])
            ->with('sukses', sprintf(
                'Absensi JP %d (%s) tanggal %s tersimpan untuk %d siswa.',
                $jadwal?->jam_ke, $jadwal?->subject?->nama, $tanggal, $jumlah,
            ));
    }

    /**
     * Sumber salinan untuk tombol cepat: status absensi umum, atau status JP
     * sebelumnya pada hari yang sama. Dikembalikan sebagai JSON agar tombol
     * bekerja tanpa memuat ulang halaman.
     */
    public function salin(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'schedule_id' => ['required', 'exists:schedules,id'],
            'dari' => ['required', Rule::in(['umum', 'jp_sebelumnya'])],
        ]);

        $tanggal = CarbonImmutable::parse($data['tanggal'])->toDateString();
        $jadwal = Schedule::findOrFail($data['schedule_id']);

        if ($data['dari'] === 'umum') {
            $sumber = DailyAttendance::where('tanggal', $tanggal)->get(['student_id', 'status', 'keterangan']);

            if ($sumber->isEmpty()) {
                return response()->json(['pesan' => 'Absensi umum hari ini belum diinput.'], 422);
            }
        } else {
            $sebelumnya = Schedule::where('hari', $jadwal->hari)
                ->where('jam_ke', '<', $jadwal->jam_ke)
                ->orderByDesc('jam_ke')
                ->first();

            if (! $sebelumnya) {
                return response()->json(['pesan' => 'Ini jam pelajaran pertama, tidak ada JP sebelumnya.'], 422);
            }

            $sumber = SubjectAttendance::where('tanggal', $tanggal)
                ->where('schedule_id', $sebelumnya->id)
                ->get(['student_id', 'status', 'keterangan']);

            if ($sumber->isEmpty()) {
                return response()->json(['pesan' => "Absensi JP {$sebelumnya->jam_ke} belum diinput."], 422);
            }
        }

        return response()->json([
            'data' => $sumber->mapWithKeys(fn ($r) => [$r->student_id => [
                'status' => $r->status->value,
                'keterangan' => $r->keterangan,
            ]]),
        ]);
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
