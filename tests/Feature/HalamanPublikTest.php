<?php

namespace Tests\Feature;

use App\Models\DailyAttendance;
use App\Models\Holiday;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HalamanPublikTest extends TestCase
{
    use RefreshDatabase;

    private Student $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::put($key, $value);
        }

        $this->siswa = Student::create([
            'no_absen' => 7,
            'nama' => 'Budi Santoso',
            'nisn' => '0098765432',
            'no_hp_ortu' => '081298765432',
            'jenis_kelamin' => 'L',
        ]);
    }

    public function test_landing_bisa_dibuka_tanpa_login(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Absensi Kelas', false)
            ->assertSee(Setting::get('nama_kelas'), false);
    }

    public function test_landing_tidak_membocorkan_data_pribadi(): void
    {
        DailyAttendance::create([
            'student_id' => $this->siswa->id,
            'tanggal' => now()->toDateString(),
            'status' => 'sakit',
        ]);

        $response = $this->get('/')->assertOk();

        $response->assertSee('Budi Santoso', false);
        $response->assertDontSee('0098765432', false);
        $response->assertDontSee('081298765432', false);
    }

    public function test_ringkasan_siswa_tidak_membocorkan_data_pribadi(): void
    {
        $response = $this->get("/siswa/{$this->siswa->id}/ringkasan")->assertOk();

        $response->assertSee('Budi Santoso', false);
        $response->assertDontSee('0098765432', false);
        $response->assertDontSee('081298765432', false);
    }

    public function test_ringkasan_siswa_nonaktif_menghasilkan_404(): void
    {
        $this->siswa->update(['is_active' => false]);

        $this->get("/siswa/{$this->siswa->id}/ringkasan")->assertNotFound();
    }

    public function test_pencarian_hanya_mengembalikan_nama_dan_no_absen(): void
    {
        $this->getJson('/siswa/cari?q=Budi')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama', 'Budi Santoso')
            ->assertJsonPath('data.0.no_absen', 7)
            ->assertJsonMissingPath('data.0.nisn')
            ->assertJsonMissingPath('data.0.no_hp_ortu');
    }

    public function test_pencarian_butuh_minimal_dua_huruf(): void
    {
        $this->getJson('/siswa/cari?q=B')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_pencarian_mengabaikan_siswa_nonaktif(): void
    {
        $this->siswa->update(['is_active' => false]);

        $this->getJson('/siswa/cari?q=Budi')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_endpoint_grafik_mengembalikan_agregat_saja(): void
    {
        DailyAttendance::create([
            'student_id' => $this->siswa->id,
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
        ]);

        $this->getJson('/data/grafik')
            ->assertOk()
            ->assertJsonStructure([
                'komposisi' => ['labels', 'data'],
                'tren' => ['labels', 'persen', 'tanggal'],
                'mapel' => ['labels', 'data', 'warna'],
            ])
            ->assertJsonMissingPath('komposisi.nama');
    }

    public function test_halaman_publik_tidak_menampilkan_menu_admin(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('Manajemen Admin', false)
            ->assertDontSee('Input Absensi', false);
    }

    public function test_halaman_publik_menampilkan_keterangan_libur_saat_hari_ini_libur(): void
    {
        Holiday::create([
            'tanggal' => now()->toDateString(),
            'keterangan' => 'Libur Nasional Kemerdekaan',
        ]);

        $response = $this->get('/')->assertOk();

        $response->assertSee('Hari Ini Libur', false);
        $response->assertSee('Libur Nasional Kemerdekaan', false);
        $response->assertDontSee('Siswa Tidak Hadir Hari Ini', false);
        $response->assertSee('Libur', false);
    }

    public function test_rekapitulasi_siswa_dan_pagination_tampil_pada_halaman_publik_saat_ada_siswa(): void
    {
        $response = $this->get('/')->assertOk();

        // Rekapitulasi harus memuat nama siswa dan pagination jika ada siswa
        $response->assertSee('Rekapitulasi Kehadiran Siswa', false);
        $response->assertSee('Budi Santoso', false);
        $response->assertSee('Menampilkan', false);
        $response->assertDontSee('Tidak ada siswa ditemukan', false);
    }

    public function test_rekapitulasi_siswa_saat_pencarian_ditemukan_dan_tidak_ditemukan(): void
    {
        // Cari nama yang cocok
        $found = $this->get('/?cari=Budi')->assertOk();
        $found->assertSee('Budi Santoso', false);
        $found->assertDontSee('Tidak ada siswa ditemukan', false);

        // Cari nama yang tidak ada
        $notFound = $this->get('/?cari=NamaTidakAda')->assertOk();
        $notFound->assertSee('Tidak ada siswa ditemukan', false);
        $notFound->assertDontSee('Menampilkan', false);
    }

    public function test_rekapitulasi_siswa_filter_semester_ini_tampil_dengan_benar(): void
    {
        $response = $this->get('/?filter_rekap=semester')->assertOk();

        $response->assertSee('Semester Ini', false);
        $response->assertSee('Kehadiran Normal (Semester Ini)', false);
        $response->assertSee('Kehadiran Mapel (Semester Ini)', false);
        $response->assertSee('Budi Santoso', false);
    }
}



