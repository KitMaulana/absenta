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
use Illuminate\Support\Collection;
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

        // Status absensi umum hari itu dipakai sebagai nilai default
        $umum = DailyAttendance::where('tanggal', $tanggal->toDateString())
            ->pluck('status', 'student_id')
            ->map(fn ($s) => $s instanceof AttendanceStatus ? $s->value : $s);

        // Kelompokkan jadwal menjadi Sesi / Blok Mata Pelajaran (per mapel, bukan per JP individual)
        $sesiMapel = $this->kelompokkanSesiMapel($jadwal, $tersimpan);

        // Cari sesi aktif dari request, atau default ke sesi pertama
        $sesiAktif = $request->input('sesi', $sesiMapel->first()->id ?? null);
        if ($request->filled('jp') && ! $request->filled('sesi')) {
            $targetJp = (int) $request->input('jp');
            foreach ($sesiMapel as $s) {
                if (in_array($targetJp, $s->jam_ke_list, true)) {
                    $sesiAktif = $s->id;
                    break;
                }
            }
        }

        return view('admin.absensi-mapel.index', [
            'tanggal' => $tanggal,
            'namaHari' => Schedule::HARI[$hari],
            'jadwal' => $jadwal,
            'sesiMapel' => $sesiMapel,
            'sesiAktif' => $sesiAktif,
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
            'schedule_ids' => ['nullable', 'array'],
            'schedule_ids.*' => ['integer', 'exists:schedules,id'],
            'schedule_id' => ['nullable', 'exists:schedules,id'],
            'sesi_id' => ['nullable', 'string'],
            'status' => ['required', 'array'],
            'status.*' => [Rule::in(AttendanceStatus::values())],
            'keterangan' => ['array'],
            'keterangan.*' => ['nullable', 'string', 'max:150'],
        ], [
            'tanggal.before_or_equal' => 'Absensi tidak bisa diisi untuk tanggal yang belum terjadi.',
        ]);

        // Ambil daftar schedule_id: bisa berupa array schedule_ids atau schedule_id tunggal
        $scheduleIds = [];
        if (! empty($data['schedule_ids'])) {
            $scheduleIds = array_map('intval', $data['schedule_ids']);
        } elseif (! empty($data['schedule_id'])) {
            $scheduleIds = [(int) $data['schedule_id']];
        }

        if (empty($scheduleIds)) {
            return back()->with('gagal', 'Jadwal pelajaran tidak valid.');
        }

        $tanggal = CarbonImmutable::parse($data['tanggal'])->toDateString();
        $idSiswaAktif = Student::aktif()->pluck('id')->flip();
        $userId = $request->user()->id;
        $jumlahSiswa = 0;

        DB::transaction(function () use ($data, $tanggal, $scheduleIds, $idSiswaAktif, $userId, &$jumlahSiswa) {
            foreach ($scheduleIds as $scheduleId) {
                foreach ($data['status'] as $studentId => $status) {
                    if (! $idSiswaAktif->has((int) $studentId)) {
                        continue;
                    }

                    SubjectAttendance::updateOrCreate(
                        [
                            'student_id' => (int) $studentId,
                            'tanggal' => $tanggal,
                            'schedule_id' => $scheduleId,
                        ],
                        [
                            'status' => $status,
                            'keterangan' => $status === AttendanceStatus::Hadir->value
                                ? null
                                : ($data['keterangan'][$studentId] ?? null),
                            'created_by' => $userId,
                        ]
                    );
                }
            }

            $jumlahSiswa = count(array_intersect_key($data['status'], $idSiswaAktif->all()));
        });

        $schedules = Schedule::with('subject')->whereIn('id', $scheduleIds)->orderBy('jam_ke')->get();
        $firstSchedule = $schedules->first();
        $mapelNama = $firstSchedule?->subject?->nama ?? 'Mata Pelajaran';
        $totalJp = $schedules->count();
        $jamKeList = $schedules->pluck('jam_ke')->implode(', ');

        $pesan = sprintf(
            'Absensi %s (%d JP: JP %s) tanggal %s berhasil disimpan untuk %d siswa.',
            $mapelNama,
            $totalJp,
            $jamKeList,
            $tanggal,
            $jumlahSiswa
        );

        $redirectParams = ['tanggal' => $tanggal];
        if (! empty($data['sesi_id'])) {
            $redirectParams['sesi'] = $data['sesi_id'];
        } else {
            $redirectParams['jp'] = $firstSchedule?->jam_ke;
        }

        return redirect()->route('admin.absensi-mapel.index', $redirectParams)->with('sukses', $pesan);
    }

    /**
     * Sumber salinan untuk tombol cepat: status absensi umum, atau status JP / sesi
     * sebelumnya pada hari yang sama.
     */
    public function salin(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'schedule_id' => ['nullable', 'exists:schedules,id'],
            'schedule_ids' => ['nullable', 'array'],
            'schedule_ids.*' => ['integer', 'exists:schedules,id'],
            'dari' => ['required', Rule::in(['umum', 'jp_sebelumnya'])],
        ]);

        $tanggal = CarbonImmutable::parse($data['tanggal'])->toDateString();

        $scheduleId = ! empty($data['schedule_ids'])
            ? (int) $data['schedule_ids'][0]
            : (int) ($data['schedule_id'] ?? 0);

        $jadwal = Schedule::findOrFail($scheduleId);

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
                return response()->json(['pesan' => "Absensi mata pelajaran sebelumnya (JP {$sebelumnya->jam_ke}) belum diinput."], 422);
            }
        }

        return response()->json([
            'data' => $sumber->mapWithKeys(fn ($r) => [$r->student_id => [
                'status' => $r->status instanceof AttendanceStatus ? $r->status->value : (string) $r->status,
                'keterangan' => $r->keterangan,
            ]]),
        ]);
    }

    /**
     * Mengelompokkan daftar JP berurutan dengan mata pelajaran yang sama menjadi satu Sesi Mata Pelajaran
     */
    private function kelompokkanSesiMapel(Collection $jadwal, Collection $tersimpan): Collection
    {
        $sesiList = collect();
        $currentBlock = collect();

        foreach ($jadwal as $j) {
            if ($currentBlock->isEmpty()) {
                $currentBlock->push($j);
                continue;
            }

            $last = $currentBlock->last();
            // Kelompokkan jika mata pelajaran sama dan jam berurutan
            if ($last->subject_id === $j->subject_id && $j->jam_ke === $last->jam_ke + 1) {
                $currentBlock->push($j);
            } else {
                $sesiList->push($this->formatSesiObject($currentBlock, $sesiList->count() + 1, $tersimpan));
                $currentBlock = collect([$j]);
            }
        }

        if ($currentBlock->isNotEmpty()) {
            $sesiList->push($this->formatSesiObject($currentBlock, $sesiList->count() + 1, $tersimpan));
        }

        return $sesiList;
    }

    private function formatSesiObject(Collection $schedules, int $urutan, Collection $tersimpan): object
    {
        $first = $schedules->first();
        $last = $schedules->last();
        $jamKeList = $schedules->pluck('jam_ke')->all();
        $totalJp = count($jamKeList);
        $scheduleIds = $schedules->pluck('id')->all();

        $labelJp = $totalJp > 1
            ? sprintf('JP %d–%d (%d JP)', min($jamKeList), max($jamKeList), $totalJp)
            : sprintf('JP %d (1 JP)', $first->jam_ke);

        $jamMulai = $first->jam_mulai ? substr((string) $first->jam_mulai, 0, 5) : null;
        $jamSelesai = $last->jam_selesai ? substr((string) $last->jam_selesai, 0, 5) : null;
        $rentangWaktu = ($jamMulai && $jamSelesai) ? "{$jamMulai}–{$jamSelesai}" : null;

        // Cek keterisian: apakah seluruh JP dalam sesi ini sudah terisi?
        $terisiSemua = collect($scheduleIds)->every(fn ($id) => $tersimpan->has($id));
        $terisiSebagian = collect($scheduleIds)->some(fn ($id) => $tersimpan->has($id));

        // Ambil data absensi tersimpan (dari schedule pertama yang memiliki data)
        $dataTersimpan = null;
        foreach ($scheduleIds as $id) {
            if ($tersimpan->has($id)) {
                $dataTersimpan = $tersimpan->get($id);
                break;
            }
        }

        $terakhirDiperbarui = null;
        if ($dataTersimpan) {
            $terakhirDiperbarui = $dataTersimpan->max('updated_at');
        }

        return (object) [
            'id' => 'sesi_' . $urutan,
            'urutan' => $urutan,
            'subject' => $first->subject,
            'subject_id' => $first->subject_id,
            'guru_pengampu' => $first->guru_pengampu ?? $last->guru_pengampu,
            'schedules' => $schedules,
            'schedule_ids' => $scheduleIds,
            'first_schedule_id' => $first->id,
            'jam_ke_list' => $jamKeList,
            'label_jp' => $labelJp,
            'total_jp' => $totalJp,
            'rentang_waktu' => $rentangWaktu,
            'terisi' => $terisiSemua,
            'terisi_sebagian' => $terisiSebagian && ! $terisiSemua,
            'data_tersimpan' => $dataTersimpan,
            'terakhir_diperbarui' => $terakhirDiperbarui,
        ];
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
