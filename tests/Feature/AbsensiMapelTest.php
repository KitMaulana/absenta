<?php

namespace Tests\Feature;

use App\Models\DailyAttendance;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAttendance;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiMapelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Student $siswa;

    /** Tanggal uji dikunci ke Senin agar jadwal hari itu selalu ada. */
    private CarbonImmutable $senin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Sekretaris', 'email' => 'sek@test.id',
            'password' => 'password', 'role' => 'sekretaris', 'is_active' => true,
        ]);

        $this->siswa = Student::create(['no_absen' => 1, 'nama' => 'Ani', 'jenis_kelamin' => 'P']);
        $this->senin = CarbonImmutable::today()->startOfWeek();
    }

    private function jadwal(int $jamKe): Schedule
    {
        $mapel = Subject::firstOrCreate(
            ['singkatan' => 'MTK'],
            ['nama' => 'Matematika', 'warna' => '#3b82f6', 'is_active' => true],
        );

        return Schedule::create([
            'hari' => 'senin', 'jam_ke' => $jamKe, 'subject_id' => $mapel->id,
        ]);
    }

    public function test_menyimpan_absensi_per_jp(): void
    {
        $jadwal = $this->jadwal(1);

        $this->actingAs($this->admin)
            ->post('/admin/absensi-mapel', [
                'tanggal' => $this->senin->toDateString(),
                'schedule_id' => $jadwal->id,
                'status' => [$this->siswa->id => 'alpa'],
                'keterangan' => [$this->siswa->id => 'Tidak masuk kelas'],
            ])
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $this->assertDatabaseHas('subject_attendances', [
            'student_id' => $this->siswa->id,
            'schedule_id' => $jadwal->id,
            'status' => 'alpa',
            'keterangan' => 'Tidak masuk kelas',
        ]);
    }

    public function test_upsert_per_jp_tidak_menggandakan(): void
    {
        $jadwal = $this->jadwal(1);

        foreach (['alpa', 'izin'] as $status) {
            $this->actingAs($this->admin)->post('/admin/absensi-mapel', [
                'tanggal' => $this->senin->toDateString(),
                'schedule_id' => $jadwal->id,
                'status' => [$this->siswa->id => $status],
            ]);
        }

        $this->assertDatabaseCount('subject_attendances', 1);
        $this->assertSame('izin', SubjectAttendance::first()->status->value);
    }

    public function test_salin_dari_absensi_umum(): void
    {
        $jadwal = $this->jadwal(1);

        DailyAttendance::create([
            'student_id' => $this->siswa->id,
            'tanggal' => $this->senin->toDateString(),
            'status' => 'sakit',
            'keterangan' => 'Demam',
        ]);

        $this->actingAs($this->admin)
            ->postJson('/admin/absensi-mapel/salin', [
                'tanggal' => $this->senin->toDateString(),
                'schedule_id' => $jadwal->id,
                'dari' => 'umum',
            ])
            ->assertOk()
            ->assertJsonPath("data.{$this->siswa->id}.status", 'sakit')
            ->assertJsonPath("data.{$this->siswa->id}.keterangan", 'Demam');
    }

    public function test_salin_dari_umum_gagal_bila_belum_diinput(): void
    {
        $jadwal = $this->jadwal(1);

        $this->actingAs($this->admin)
            ->postJson('/admin/absensi-mapel/salin', [
                'tanggal' => $this->senin->toDateString(),
                'schedule_id' => $jadwal->id,
                'dari' => 'umum',
            ])
            ->assertStatus(422)
            ->assertJsonPath('pesan', 'Absensi umum hari ini belum diinput.');
    }

    public function test_salin_dari_jp_sebelumnya(): void
    {
        $jp1 = $this->jadwal(1);
        $jp2 = $this->jadwal(2);

        SubjectAttendance::create([
            'student_id' => $this->siswa->id,
            'tanggal' => $this->senin->toDateString(),
            'schedule_id' => $jp1->id,
            'status' => 'dispensasi',
        ]);

        $this->actingAs($this->admin)
            ->postJson('/admin/absensi-mapel/salin', [
                'tanggal' => $this->senin->toDateString(),
                'schedule_id' => $jp2->id,
                'dari' => 'jp_sebelumnya',
            ])
            ->assertOk()
            ->assertJsonPath("data.{$this->siswa->id}.status", 'dispensasi');
    }

    public function test_salin_jp_sebelumnya_ditolak_pada_jam_pertama(): void
    {
        $jp1 = $this->jadwal(1);

        $this->actingAs($this->admin)
            ->postJson('/admin/absensi-mapel/salin', [
                'tanggal' => $this->senin->toDateString(),
                'schedule_id' => $jp1->id,
                'dari' => 'jp_sebelumnya',
            ])
            ->assertStatus(422)
            ->assertJsonPath('pesan', 'Ini jam pelajaran pertama, tidak ada JP sebelumnya.');
    }

    public function test_form_memakai_status_absensi_umum_sebagai_default(): void
    {
        $this->jadwal(1);

        DailyAttendance::create([
            'student_id' => $this->siswa->id,
            'tanggal' => $this->senin->toDateString(),
            'status' => 'sakit',
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/absensi-mapel?tanggal='.$this->senin->toDateString())
            ->assertOk()
            ->assertSee('Absensi umum hari ini sudah diinput', false);
    }

    public function test_menyimpan_absensi_per_mapel_sekaligus_ke_seluruh_jp(): void
    {
        // Simulasi mapel Bahasa Indonesia 3 JP (JP 1, JP 2, JP 3)
        $jp1 = $this->jadwal(1);
        $jp2 = $this->jadwal(2);
        $jp3 = $this->jadwal(3);

        $this->actingAs($this->admin)
            ->post('/admin/absensi-mapel', [
                'tanggal' => $this->senin->toDateString(),
                'schedule_ids' => [$jp1->id, $jp2->id, $jp3->id],
                'status' => [$this->siswa->id => 'hadir'],
            ])
            ->assertRedirect()
            ->assertSessionHas('sukses');

        // Pastikan di ketiga JP siswa tercatat hadir
        $this->assertDatabaseCount('subject_attendances', 3);
        foreach ([$jp1->id, $jp2->id, $jp3->id] as $scheduleId) {
            $this->assertDatabaseHas('subject_attendances', [
                'student_id' => $this->siswa->id,
                'schedule_id' => $scheduleId,
                'status' => 'hadir',
            ]);
        }
    }

    public function test_menyimpan_absensi_per_mapel_status_tidak_hadir_ke_seluruh_jp(): void
    {
        $jp1 = $this->jadwal(1);
        $jp2 = $this->jadwal(2);
        $jp3 = $this->jadwal(3);

        $this->actingAs($this->admin)
            ->post('/admin/absensi-mapel', [
                'tanggal' => $this->senin->toDateString(),
                'schedule_ids' => [$jp1->id, $jp2->id, $jp3->id],
                'status' => [$this->siswa->id => 'sakit'],
                'keterangan' => [$this->siswa->id => 'Demam tinggi'],
            ])
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $this->assertDatabaseCount('subject_attendances', 3);
        foreach ([$jp1->id, $jp2->id, $jp3->id] as $scheduleId) {
            $this->assertDatabaseHas('subject_attendances', [
                'student_id' => $this->siswa->id,
                'schedule_id' => $scheduleId,
                'status' => 'sakit',
                'keterangan' => 'Demam tinggi',
            ]);
        }
    }
}

