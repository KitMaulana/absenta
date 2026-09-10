<?php

namespace Tests\Feature;

use App\Models\DailyAttendance;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MasterDataTest extends TestCase
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
            'name' => 'Wali', 'email' => 'wali@test.id',
            'password' => 'password', 'role' => 'wali_kelas', 'is_active' => true,
        ]);
    }

    public function test_siswa_dengan_riwayat_absensi_tidak_bisa_dihapus(): void
    {
        $siswa = Student::create(['no_absen' => 1, 'nama' => 'Ani', 'jenis_kelamin' => 'P']);

        DailyAttendance::create([
            'student_id' => $siswa->id,
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
        ]);

        $this->actingAs($this->wali)
            ->delete("/admin/siswa/{$siswa->id}")
            ->assertSessionHas('gagal');

        $this->assertDatabaseHas('students', ['id' => $siswa->id]);
    }

    public function test_siswa_tanpa_riwayat_bisa_dihapus(): void
    {
        $siswa = Student::create(['no_absen' => 1, 'nama' => 'Ani', 'jenis_kelamin' => 'P']);

        $this->actingAs($this->wali)
            ->delete("/admin/siswa/{$siswa->id}")
            ->assertSessionHas('sukses');

        $this->assertDatabaseMissing('students', ['id' => $siswa->id]);
    }

    public function test_menonaktifkan_siswa_mempertahankan_riwayat(): void
    {
        $siswa = Student::create(['no_absen' => 1, 'nama' => 'Ani', 'jenis_kelamin' => 'P']);
        DailyAttendance::create([
            'student_id' => $siswa->id, 'tanggal' => now()->toDateString(), 'status' => 'alpa',
        ]);

        $this->actingAs($this->wali)
            ->patch("/admin/siswa/{$siswa->id}/toggle")
            ->assertSessionHas('sukses');

        $this->assertFalse($siswa->fresh()->is_active);
        $this->assertDatabaseCount('daily_attendances', 1);
    }

    public function test_import_csv_menambahkan_siswa(): void
    {
        $csv = "no_absen,nama,nisn,jenis_kelamin\n1,Ahmad Fauzan,0012345678,L\n2,Alya Rahma,0012345679,P\n";

        $this->actingAs($this->wali)
            ->post('/admin/siswa/import', [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
                'mode' => 'tambah',
            ])
            ->assertSessionHas('sukses');

        $this->assertDatabaseCount('students', 2);
        $this->assertDatabaseHas('students', ['nama' => 'Ahmad Fauzan', 'nisn' => '0012345678', 'jenis_kelamin' => 'L']);
    }

    public function test_import_csv_melewati_baris_tidak_valid(): void
    {
        $csv = "no_absen,nama,jenis_kelamin\n1,Valid Satu,L\n2,,P\n3,Jenis Salah,X\n4,Valid Dua,P\n";

        $this->actingAs($this->wali)->post('/admin/siswa/import', [
            'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
            'mode' => 'tambah',
        ]);

        $this->assertDatabaseCount('students', 2);
        $this->assertDatabaseHas('students', ['nama' => 'Valid Satu']);
        $this->assertDatabaseHas('students', ['nama' => 'Valid Dua']);
    }

    public function test_import_csv_menolak_header_tanpa_kolom_wajib(): void
    {
        $csv = "nama,kelas\nAhmad,XII\n";

        $this->actingAs($this->wali)
            ->post('/admin/siswa/import', [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
                'mode' => 'tambah',
            ])
            ->assertSessionHas('gagal');

        $this->assertDatabaseCount('students', 0);
    }

    public function test_import_csv_memperbarui_siswa_dengan_nisn_sama_tanpa_duplicate_exception(): void
    {
        Student::create([
            'no_absen' => 10,
            'nama' => 'Nida Nur Faida',
            'nisn' => '0084668554',
            'jenis_kelamin' => 'P',
        ]);

        $csv = "no_absen,nama,nisn,jenis_kelamin\n35,NIDA NUR FAIDA,0084668554,P\n";

        $this->actingAs($this->wali)
            ->post('/admin/siswa/import', [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
                'mode' => 'tambah',
            ])
            ->assertSessionHas('sukses');

        $this->assertDatabaseCount('students', 1);
        $this->assertDatabaseHas('students', [
            'nama' => 'NIDA NUR FAIDA',
            'no_absen' => 35,
            'nisn' => '0084668554',
            'jenis_kelamin' => 'P',
        ]);
    }

    public function test_import_csv_mendukung_format_excel_petik_pada_nisn(): void
    {
        $csv = "no_absen,nama,nisn,jenis_kelamin\n1,Budi Santoso,'0084668555,L\n";

        $this->actingAs($this->wali)
            ->post('/admin/siswa/import', [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
                'mode' => 'tambah',
            ])
            ->assertSessionHas('sukses');

        $this->assertDatabaseHas('students', [
            'nama' => 'Budi Santoso',
            'nisn' => '0084668555',
        ]);
    }

    public function test_import_csv_melewati_nisn_duplikat_dalam_file(): void
    {
        $csv = "no_absen,nama,nisn,jenis_kelamin\n1,Siswa Satu,0011223344,L\n2,Siswa Dua,0011223344,L\n";

        $this->actingAs($this->wali)
            ->post('/admin/siswa/import', [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
                'mode' => 'tambah',
            ])
            ->assertSessionHas('sukses');

        $this->assertDatabaseCount('students', 1);
        $this->assertDatabaseHas('students', ['nama' => 'Siswa Satu']);
    }

    public function test_import_csv_mendukung_delimiter_titik_koma(): void
    {
        $csv = "no_absen;nama;nisn;jenis_kelamin\n1;Citra Lestari;0099887766;P\n";

        $this->actingAs($this->wali)
            ->post('/admin/siswa/import', [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
                'mode' => 'tambah',
            ])
            ->assertSessionHas('sukses');

        $this->assertDatabaseCount('students', 1);
        $this->assertDatabaseHas('students', [
            'nama' => 'Citra Lestari',
            'nisn' => '0099887766',
        ]);
    }

    public function test_import_csv_mode_ganti_mempertahankan_siswa_berriwayat_dan_memperbarui_nisn(): void
    {
        $lama = Student::create([
            'no_absen' => 1,
            'nama' => 'Nida Nur Faida',
            'nisn' => '0084668554',
            'jenis_kelamin' => 'P',
        ]);

        DailyAttendance::create([
            'student_id' => $lama->id,
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
        ]);

        $csv = "no_absen,nama,nisn,jenis_kelamin\n35,NIDA NUR FAIDA,0084668554,P\n";

        $this->actingAs($this->wali)
            ->post('/admin/siswa/import', [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
                'mode' => 'ganti',
            ])
            ->assertSessionHas('sukses');

        $this->assertDatabaseCount('students', 1);
        $this->assertTrue($lama->fresh()->is_active);
        $this->assertSame('NIDA NUR FAIDA', $lama->fresh()->nama);
        $this->assertSame(35, $lama->fresh()->no_absen);
    }

    public function test_export_csv_berisi_header_dan_data(): void
    {
        Student::create(['no_absen' => 3, 'nama' => 'Citra', 'jenis_kelamin' => 'P', 'nisn' => '0011']);

        $response = $this->actingAs($this->wali)->get('/admin/siswa/export')->assertOk();
        $isi = $response->streamedContent();

        $this->assertStringContainsString('no_absen,nama,nisn,jenis_kelamin', $isi);
        $this->assertStringContainsString('Citra', $isi);
    }

    public function test_jadwal_menolak_jam_ke_ganda_pada_hari_sama(): void
    {
        $mapel = Subject::create(['nama' => 'Matematika', 'singkatan' => 'MTK', 'warna' => '#3b82f6']);
        Schedule::create(['hari' => 'senin', 'jam_ke' => 1, 'subject_id' => $mapel->id]);

        $this->actingAs($this->wali)
            ->post('/admin/jadwal', ['hari' => 'senin', 'jam_ke' => 1, 'subject_id' => $mapel->id])
            ->assertSessionHasErrors('jam_ke');

        $this->assertDatabaseCount('schedules', 1);
    }

    public function test_jadwal_menolak_jam_selesai_sebelum_jam_mulai(): void
    {
        $mapel = Subject::create(['nama' => 'Fisika', 'singkatan' => 'FIS', 'warna' => '#8b5cf6']);

        $this->actingAs($this->wali)
            ->post('/admin/jadwal', [
                'hari' => 'selasa', 'jam_ke' => 1, 'subject_id' => $mapel->id,
                'jam_mulai' => '09:00', 'jam_selesai' => '08:00',
            ])
            ->assertSessionHasErrors('jam_selesai');
    }

    public function test_mapel_yang_dipakai_jadwal_tidak_bisa_dihapus(): void
    {
        $mapel = Subject::create(['nama' => 'Kimia', 'singkatan' => 'KIM', 'warna' => '#14b8a6']);
        Schedule::create(['hari' => 'rabu', 'jam_ke' => 1, 'subject_id' => $mapel->id]);

        $this->actingAs($this->wali)
            ->delete("/admin/mapel/{$mapel->id}")
            ->assertSessionHas('gagal');

        $this->assertDatabaseHas('subjects', ['id' => $mapel->id]);
    }

    public function test_mapel_menolak_warna_bukan_heksadesimal(): void
    {
        $this->actingAs($this->wali)
            ->post('/admin/mapel', ['nama' => 'Biologi', 'singkatan' => 'BIO', 'warna' => 'merah'])
            ->assertSessionHasErrors('warna');
    }

    public function test_hari_libur_menolak_tanggal_ganda(): void
    {
        $this->actingAs($this->wali)->post('/admin/libur', [
            'tanggal' => '2026-12-25', 'keterangan' => 'Libur Natal',
        ])->assertSessionHas('sukses');

        $this->actingAs($this->wali)
            ->post('/admin/libur', ['tanggal' => '2026-12-25', 'keterangan' => 'Ganda'])
            ->assertSessionHasErrors('tanggal');

        $this->assertDatabaseCount('holidays', 1);
    }

    public function test_pengaturan_kelas_tersimpan_dan_cache_diperbarui(): void
    {
        $this->actingAs($this->wali)
            ->put('/admin/pengaturan', [
                'nama_kelas' => 'XI IPS 2',
                'nama_sekolah' => 'SMAN 5 Contoh',
                'nama_wali_kelas' => 'Ibu Sari',
                'tahun_ajaran' => '2027/2028',
                'semester' => 'Genap',
                'hari_aktif' => ['senin', 'selasa', 'rabu'],
            ])
            ->assertSessionHas('sukses');

        $this->assertSame('XI IPS 2', Setting::get('nama_kelas'));
        $this->assertSame(['senin', 'selasa', 'rabu'], Setting::hariAktif());
    }

    public function test_pengaturan_menolak_hari_aktif_kosong(): void
    {
        $this->actingAs($this->wali)
            ->put('/admin/pengaturan', [
                'nama_kelas' => 'XI IPS 2',
                'nama_wali_kelas' => 'Ibu Sari',
                'tahun_ajaran' => '2027/2028',
                'semester' => 'Genap',
            ])
            ->assertSessionHasErrors('hari_aktif');
    }

    public function test_unggah_logo_tersimpan_di_disk_publik(): void
    {
        Storage::fake('public');

        $this->actingAs($this->wali)->put('/admin/pengaturan', [
            'nama_kelas' => 'XII IPA 3',
            'nama_wali_kelas' => 'Pak Budi',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'hari_aktif' => ['senin'],
            'logo' => UploadedFile::fake()->image('logo.png', 64, 64),
        ])->assertSessionHas('sukses');

        $tersimpan = Setting::get('logo');
        $this->assertNotEmpty($tersimpan);
        Storage::disk('public')->assertExists($tersimpan);
    }

    public function test_reset_total_siswa_menghapus_semua_siswa_dan_absensi(): void
    {
        $siswa1 = Student::create(['no_absen' => 1, 'nama' => 'Ani', 'jenis_kelamin' => 'P']);
        $siswa2 = Student::create(['no_absen' => 2, 'nama' => 'Budi', 'jenis_kelamin' => 'L']);

        DailyAttendance::create([
            'student_id' => $siswa1->id,
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
        ]);

        $this->actingAs($this->wali)
            ->delete('/admin/siswa/reset', [
                'konfirmasi' => 'HAPUS',
                'hapus_absensi' => 1,
            ])
            ->assertSessionHas('sukses')
            ->assertRedirect(route('admin.siswa.index'));

        $this->assertDatabaseCount('students', 0);
        $this->assertDatabaseCount('daily_attendances', 0);
    }

    public function test_reset_total_siswa_ditolak_jika_konfirmasi_salah(): void
    {
        Student::create(['no_absen' => 1, 'nama' => 'Ani', 'jenis_kelamin' => 'P']);

        $this->actingAs($this->wali)
            ->delete('/admin/siswa/reset', [
                'konfirmasi' => 'SALAH',
            ])
            ->assertSessionHasErrors('konfirmasi');

        $this->assertDatabaseCount('students', 1);
    }

    public function test_reset_total_siswa_ditolak_jika_data_kosong(): void
    {
        $this->actingAs($this->wali)
            ->delete('/admin/siswa/reset', [
                'konfirmasi' => 'HAPUS',
            ])
            ->assertSessionHas('gagal');
    }
}
