<?php

namespace Tests\Feature;

use App\Models\DailyAttendance;
use App\Models\Holiday;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiUmumTest extends TestCase
{
    use RefreshDatabase;

    private function siswa(int $jumlah = 3): \Illuminate\Support\Collection
    {
        return collect(range(1, $jumlah))->map(fn ($i) => Student::create([
            'no_absen' => $i,
            'nama' => "Siswa {$i}",
            'jenis_kelamin' => $i % 2 === 0 ? 'P' : 'L',
        ]));
    }

    private function ketua(): User
    {
        return User::create([
            'name' => 'Ketua', 'email' => 'ketua@test.id',
            'password' => 'password', 'role' => 'ketua_kelas', 'is_active' => true,
        ]);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/admin/absensi-umum')->assertRedirect('/login');
    }

    public function test_menyimpan_absensi_harian(): void
    {
        $siswa = $this->siswa();
        $tanggal = now()->toDateString();

        $this->actingAs($this->ketua())
            ->post('/admin/absensi-umum', [
                'tanggal' => $tanggal,
                'status' => [
                    $siswa[0]->id => 'hadir',
                    $siswa[1]->id => 'sakit',
                    $siswa[2]->id => 'alpa',
                ],
                'keterangan' => [$siswa[1]->id => 'Surat dokter'],
            ])
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $this->assertDatabaseCount('daily_attendances', 3);
        $this->assertDatabaseHas('daily_attendances', [
            'student_id' => $siswa[1]->id, 'status' => 'sakit', 'keterangan' => 'Surat dokter',
        ]);
    }

    public function test_keterangan_dibuang_untuk_siswa_hadir(): void
    {
        $siswa = $this->siswa(1);

        $this->actingAs($this->ketua())->post('/admin/absensi-umum', [
            'tanggal' => now()->toDateString(),
            'status' => [$siswa[0]->id => 'hadir'],
            'keterangan' => [$siswa[0]->id => 'tidak relevan'],
        ]);

        $this->assertNull(DailyAttendance::first()->keterangan);
    }

    public function test_menyimpan_ulang_memperbarui_bukan_menggandakan(): void
    {
        $siswa = $this->siswa(1);
        $tanggal = now()->toDateString();
        $ketua = $this->ketua();

        $this->actingAs($ketua)->post('/admin/absensi-umum', [
            'tanggal' => $tanggal,
            'status' => [$siswa[0]->id => 'alpa'],
        ]);

        $this->actingAs($ketua)->post('/admin/absensi-umum', [
            'tanggal' => $tanggal,
            'status' => [$siswa[0]->id => 'izin'],
        ]);

        $this->assertDatabaseCount('daily_attendances', 1);
        $this->assertSame('izin', DailyAttendance::first()->status->value);
    }

    public function test_menolak_tanggal_di_masa_depan(): void
    {
        $siswa = $this->siswa(1);

        $this->actingAs($this->ketua())
            ->post('/admin/absensi-umum', [
                'tanggal' => now()->addDay()->toDateString(),
                'status' => [$siswa[0]->id => 'hadir'],
            ])
            ->assertSessionHasErrors('tanggal');

        $this->assertDatabaseCount('daily_attendances', 0);
    }

    public function test_mengabaikan_siswa_nonaktif(): void
    {
        $siswa = $this->siswa(2);
        $siswa[1]->update(['is_active' => false]);

        $this->actingAs($this->ketua())->post('/admin/absensi-umum', [
            'tanggal' => now()->toDateString(),
            'status' => [$siswa[0]->id => 'hadir', $siswa[1]->id => 'hadir'],
        ]);

        $this->assertDatabaseCount('daily_attendances', 1);
    }

    public function test_menolak_status_tidak_dikenal(): void
    {
        $siswa = $this->siswa(1);

        $this->actingAs($this->ketua())
            ->post('/admin/absensi-umum', [
                'tanggal' => now()->toDateString(),
                'status' => [$siswa[0]->id => 'bolos'],
            ])
            ->assertSessionHasErrors();
    }

    public function test_menetapkan_hari_libur_dan_membersihkan_data_presensi(): void
    {
        $siswa = $this->siswa(2);
        $tanggal = now()->toDateString();
        $ketua = $this->ketua();

        // Buat absensi umum sebelumnya
        DailyAttendance::create([
            'student_id' => $siswa[0]->id,
            'tanggal' => $tanggal,
            'status' => 'hadir',
        ]);

        // Buat jadwal dan absensi mapel sebelumnya
        $mapel = Subject::create(['nama' => 'IPA', 'singkatan' => 'IPA', 'warna' => '#10b981', 'is_active' => true]);
        $jadwal = Schedule::create(['hari' => 'senin', 'jam_ke' => 1, 'subject_id' => $mapel->id]);
        SubjectAttendance::create([
            'student_id' => $siswa[0]->id,
            'tanggal' => $tanggal,
            'schedule_id' => $jadwal->id,
            'status' => 'hadir',
        ]);

        $this->assertDatabaseCount('daily_attendances', 1);
        $this->assertDatabaseCount('subject_attendances', 1);

        // Tetapkan sebagai hari libur
        $this->actingAs($ketua)
            ->post('/admin/absensi-umum/libur', [
                'tanggal' => $tanggal,
                'keterangan' => 'Libur Nasional Maulid',
            ])
            ->assertRedirect()
            ->assertSessionHas('sukses');

        // Pastikan tabel holidays mencatat tanggal dan keterangan
        $this->assertDatabaseHas('holidays', [
            'tanggal' => $tanggal,
            'keterangan' => 'Libur Nasional Maulid',
        ]);

        // Pastikan seluruh absensi pada tanggal tersebut otomatis dibersihkan
        $this->assertDatabaseCount('daily_attendances', 0);
        $this->assertDatabaseCount('subject_attendances', 0);
    }

    public function test_membatalkan_hari_libur(): void
    {
        $tanggal = now()->toDateString();
        Holiday::create(['tanggal' => $tanggal, 'keterangan' => 'Libur Khusus']);

        $this->assertDatabaseCount('holidays', 1);

        $this->actingAs($this->ketua())
            ->post('/admin/absensi-umum/batal-libur', [
                'tanggal' => $tanggal,
            ])
            ->assertRedirect()
            ->assertSessionHas('sukses');

        $this->assertDatabaseCount('holidays', 0);
    }

    public function test_menolak_input_absensi_pada_hari_libur(): void
    {
        $siswa = $this->siswa(1);
        $tanggal = now()->toDateString();
        Holiday::create(['tanggal' => $tanggal, 'keterangan' => 'Libur Sekolah']);

        $this->actingAs($this->ketua())
            ->post('/admin/absensi-umum', [
                'tanggal' => $tanggal,
                'status' => [$siswa[0]->id => 'hadir'],
            ])
            ->assertRedirect()
            ->assertSessionHas('gagal');

        $this->assertDatabaseCount('daily_attendances', 0);
    }
}
