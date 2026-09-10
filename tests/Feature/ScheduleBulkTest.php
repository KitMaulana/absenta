<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ScheduleBulkTest extends TestCase
{
    use RefreshDatabase;

    private User $wali;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::put($key, $value);
        }

        $this->wali = User::create([
            'name' => 'Wali Kelas',
            'email' => 'wali@kelas.test',
            'password' => 'password',
            'role' => 'wali_kelas',
            'is_active' => true,
        ]);
    }

    public function test_download_template_csv_jadwal(): void
    {
        $response = $this->actingAs($this->wali)
            ->get('/admin/jadwal/template')
            ->assertOk();

        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_halaman_import_jadwal_dapat_diakses(): void
    {
        $this->actingAs($this->wali)
            ->get('/admin/jadwal/import')
            ->assertOk()
            ->assertSee('Import Jadwal Pelajaran')
            ->assertSee('Download Template CSV');
    }

    public function test_import_csv_jadwal_berhasil_dan_membuat_mapel_baru_otomatis(): void
    {
        $csvContent = "hari,jam_ke,mata_pelajaran,guru_pengampu,jam_mulai,jam_selesai\n"
            . "Senin,1,Fisika Modern,Pak Albert,07:00,07:45\n"
            . "Senin,2,Fisika Modern,Pak Albert,07:45,08:30\n";

        $file = UploadedFile::fake()->createWithContent('jadwal.csv', $csvContent);

        $this->actingAs($this->wali)
            ->post('/admin/jadwal/import', [
                'file' => $file,
                'mode' => 'tambah',
            ])
            ->assertRedirect('/admin/jadwal')
            ->assertSessionHas('sukses');

        $this->assertDatabaseCount('schedules', 2);
        $this->assertDatabaseHas('subjects', ['nama' => 'Fisika Modern']);
        $this->assertDatabaseHas('schedules', [
            'hari' => 'senin',
            'jam_ke' => 1,
            'guru_pengampu' => 'Pak Albert',
            'jam_mulai' => '07:00',
        ]);
        $this->assertDatabaseHas('schedules', [
            'hari' => 'senin',
            'jam_ke' => 2,
            'guru_pengampu' => 'Pak Albert',
            'jam_mulai' => '07:45',
        ]);
    }

    public function test_import_csv_jadwal_mode_ganti(): void
    {
        $mapelLama = Subject::create(['nama' => 'Kimia Lama', 'singkatan' => 'KIM', 'warna' => '#ef4444']);
        Schedule::create(['hari' => 'senin', 'jam_ke' => 1, 'subject_id' => $mapelLama->id]);

        $csvContent = "hari,jam_ke,mata_pelajaran\nSelasa,1,Biologi Baru\n";
        $file = UploadedFile::fake()->createWithContent('jadwal.csv', $csvContent);

        $this->actingAs($this->wali)
            ->post('/admin/jadwal/import', [
                'file' => $file,
                'mode' => 'ganti',
            ])
            ->assertRedirect('/admin/jadwal')
            ->assertSessionHas('sukses');

        // Jadwal lama harus terganti
        $this->assertDatabaseMissing('schedules', ['hari' => 'senin', 'jam_ke' => 1]);
        $this->assertDatabaseHas('schedules', ['hari' => 'selasa', 'jam_ke' => 1]);
    }

    public function test_tambah_massal_rentang_jp(): void
    {
        $mapel = Subject::create(['nama' => 'Bahasa Indonesia', 'singkatan' => 'BIND', 'warna' => '#ef4444']);

        $this->actingAs($this->wali)
            ->post('/admin/jadwal/tambah-massal', [
                'hari' => 'senin',
                'subject_id' => $mapel->id,
                'guru_pengampu' => 'Pak Bangkit',
                'jam_ke_mulai' => 1,
                'jam_ke_selesai' => 3,
                'jam_mulai' => '07:00',
                'jam_selesai' => '09:15',
            ])
            ->assertRedirect('/admin/jadwal')
            ->assertSessionHas('sukses');

        // Menghasilkan 3 JP sekaligus
        $this->assertDatabaseCount('schedules', 3);
        foreach ([1, 2, 3] as $jp) {
            $this->assertDatabaseHas('schedules', [
                'hari' => 'senin',
                'jam_ke' => $jp,
                'subject_id' => $mapel->id,
                'guru_pengampu' => 'Pak Bangkit',
            ]);
        }
    }

    public function test_bulk_delete_jadwal_aman_terhadap_riwayat_absensi(): void
    {
        $mapel = Subject::create(['nama' => 'Matematika', 'singkatan' => 'MTK', 'warna' => '#3b82f6']);
        $j1 = Schedule::create(['hari' => 'senin', 'jam_ke' => 1, 'subject_id' => $mapel->id]);
        $j2 = Schedule::create(['hari' => 'senin', 'jam_ke' => 2, 'subject_id' => $mapel->id]);

        $siswa = Student::create(['no_absen' => 1, 'nama' => 'Budi', 'jenis_kelamin' => 'L']);

        // J1 memiliki absensi
        SubjectAttendance::create([
            'student_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'schedule_id' => $j1->id,
            'status' => 'hadir',
        ]);

        $this->actingAs($this->wali)
            ->post('/admin/jadwal/bulk-delete', [
                'ids' => [$j1->id, $j2->id],
            ])
            ->assertRedirect('/admin/jadwal')
            ->assertSessionHas('sukses');

        // J1 tidak boleh terhapus karena punya riwayat absensi
        $this->assertDatabaseHas('schedules', ['id' => $j1->id]);
        // J2 boleh terhapus karena belum ada absensi
        $this->assertDatabaseMissing('schedules', ['id' => $j2->id]);
    }
}
