<?php

namespace Tests\Feature;

use App\Models\DailyAttendance;
use App\Models\Holiday;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAttendance;
use App\Models\User;
use App\Services\RekapService;
use App\Support\Periode;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekapDanLaporanTest extends TestCase
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

    public function test_semua_halaman_rekap_terbuka(): void
    {
        foreach (['umum', 'mapel', 'siswa', 'bulanan', 'semester', 'infografis'] as $jenis) {
            $this->actingAs($this->wali)
                ->get("/admin/rekap/{$jenis}")
                ->assertOk();
        }
    }

    public function test_jenis_rekap_tidak_dikenal_menghasilkan_404(): void
    {
        $this->actingAs($this->wali)
            ->get('/admin/rekap/ngawur')
            ->assertNotFound();
    }

    public function test_semua_pdf_menghasilkan_berkas_pdf_valid(): void
    {
        Student::create(['no_absen' => 1, 'nama' => 'Ani', 'jenis_kelamin' => 'P']);

        foreach (['umum', 'mapel', 'siswa', 'bulanan', 'semester', 'infografis'] as $jenis) {
            $response = $this->actingAs($this->wali)->get("/admin/rekap/{$jenis}/pdf");

            $response->assertOk();
            $response->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent(), "PDF {$jenis} tidak valid");
        }
    }

    public function test_hari_libur_dikeluarkan_dari_hari_efektif(): void
    {
        $rekap = app(RekapService::class);

        // Rabu di tengah pekan kerja; hari aktif default Senin–Jumat.
        $senin = CarbonImmutable::today()->startOfWeek();
        $rabu = $senin->addDays(2);

        $sebelum = $rekap->hariEfektif($senin, $senin->addDays(4));
        $this->assertCount(5, $sebelum);

        Holiday::create(['tanggal' => $rabu->toDateString(), 'keterangan' => 'Libur uji']);

        $sesudah = $rekap->hariEfektif($senin, $senin->addDays(4));
        $this->assertCount(4, $sesudah);
        $this->assertNotContains($rabu->toDateString(), $sesudah);
    }

    public function test_akhir_pekan_bukan_hari_efektif(): void
    {
        $senin = CarbonImmutable::today()->startOfWeek();
        $hari = app(RekapService::class)->hariEfektif($senin, $senin->addDays(6));

        $this->assertCount(5, $hari);
    }

    public function test_dispensasi_dihitung_sebagai_hadir_dalam_persentase(): void
    {
        $siswa = collect(range(1, 4))->map(fn ($i) => Student::create([
            'no_absen' => $i, 'nama' => "S{$i}", 'jenis_kelamin' => 'L',
        ]));

        $tanggal = CarbonImmutable::today();

        foreach (['hadir', 'dispensasi', 'sakit', 'alpa'] as $i => $status) {
            DailyAttendance::create([
                'student_id' => $siswa[$i]->id,
                'tanggal' => $tanggal->toDateString(),
                'status' => $status,
            ]);
        }

        $ringkasan = app(RekapService::class)->ringkasanHarian($tanggal);

        // hadir + dispensasi = 2 dari 4 entri.
        $this->assertSame(50.0, $ringkasan['persen']);
        $this->assertSame(4, $ringkasan['total']);
    }

    public function test_daftar_rawan_alpa_memakai_ambang_tiga(): void
    {
        $rajin = Student::create(['no_absen' => 1, 'nama' => 'Rajin', 'jenis_kelamin' => 'L']);
        $bolos = Student::create(['no_absen' => 2, 'nama' => 'Sering Bolos', 'jenis_kelamin' => 'L']);

        $mulai = CarbonImmutable::today()->subDays(10);

        for ($i = 0; $i < 3; $i++) {
            DailyAttendance::create([
                'student_id' => $bolos->id,
                'tanggal' => $mulai->addDays($i)->toDateString(),
                'status' => 'alpa',
            ]);
        }

        DailyAttendance::create([
            'student_id' => $rajin->id,
            'tanggal' => $mulai->toDateString(),
            'status' => 'alpa',
        ]);

        $hasil = app(RekapService::class)->siswaRawanAlpa($mulai, CarbonImmutable::today());

        $this->assertCount(1, $hasil);
        $this->assertSame('Sering Bolos', $hasil->first()->nama);
        $this->assertSame(3, (int) $hasil->first()->alpa);
    }

    public function test_persentase_nol_saat_belum_ada_data(): void
    {
        $ringkasan = app(RekapService::class)->ringkasanHarian(CarbonImmutable::today());

        $this->assertSame(0.0, $ringkasan['persen']);
        $this->assertFalse($ringkasan['sudah_diinput']);
    }

    public function test_hari_libur_tidak_dihitung_dalam_rekap_siswa_dan_alpa(): void
    {
        $siswa = Student::create(['no_absen' => 1, 'nama' => 'Budi', 'jenis_kelamin' => 'L']);
        $tanggal = CarbonImmutable::today()->subDays(2);

        // Siswa tercatat alpa pada tanggal tersebut
        DailyAttendance::create([
            'student_id' => $siswa->id,
            'tanggal' => $tanggal->toDateString(),
            'status' => 'alpa',
        ]);

        $rekap = app(RekapService::class);

        // Sebelum ditetapkan libur: terhitung alpa 1
        $sebelum = $rekap->perSiswa($tanggal, $tanggal)->first();
        $this->assertSame(1, $sebelum->hitung['alpa']);

        // Ditetapkan sebagai hari libur
        Holiday::create([
            'tanggal' => $tanggal->toDateString(),
            'keterangan' => 'Libur Tanggal Merah',
        ]);

        // Sesudah ditetapkan libur: alpa di hari libur TIDAK boleh dihitung
        $sesudah = $rekap->perSiswa($tanggal, $tanggal)->first();
        $this->assertSame(0, $sesudah->hitung['alpa']);
        $this->assertSame(0, $sesudah->total);

        // Di ringkasan harian juga berstatus libur dan tidak ada siswa tidak hadir
        $ringkasan = $rekap->ringkasanHarian($tanggal);
        $this->assertTrue($ringkasan['is_libur']);
        $this->assertCount(0, $rekap->tidakHadirPada($tanggal));
    }

    public function test_peringatan_semester_mendeteksi_kehadiran_dibawah_75_persen_umum_dan_mapel(): void
    {
        $siswaAman = Student::create(['no_absen' => 1, 'nama' => 'Siswa Aman', 'jenis_kelamin' => 'L', 'no_hp_ortu' => '081234567890']);
        $siswaUmumRendah = Student::create(['no_absen' => 2, 'nama' => 'Siswa Umum Rendah', 'jenis_kelamin' => 'P', 'no_hp_ortu' => '081299998888']);
        $siswaMapelRendah = Student::create(['no_absen' => 3, 'nama' => 'Siswa Bolos Mapel', 'jenis_kelamin' => 'L']);
        $siswaTanpaData = Student::create(['no_absen' => 4, 'nama' => 'Siswa Belum Diinput', 'jenis_kelamin' => 'P']);

        $matematika = Subject::create(['nama' => 'Matematika', 'singkatan' => 'MAT', 'warna' => '#2563eb', 'is_active' => true]);
        $fisika = Subject::create(['nama' => 'Fisika', 'singkatan' => 'FIS', 'warna' => '#16a34a', 'is_active' => true]);

        $jadwalMat = Schedule::create(['hari' => 'senin', 'jam_ke' => 1, 'subject_id' => $matematika->id]);
        $jadwalFis = Schedule::create(['hari' => 'senin', 'jam_ke' => 2, 'subject_id' => $fisika->id]);

        $periode = Periode::semesterRealtime(CarbonImmutable::today());
        $mulai = $periode->mulai;

        // 4 hari tercatat di dalam semester berjalan:
        // - Siswa Aman: 4/4 hadir umum (100%), 4/4 hadir MAT (100%)
        // - Siswa Umum Rendah: 2/4 hadir umum (50% < 75%), butuh +4 hari hadir berturut-turut untuk >= 75% (6/8 = 75%)
        // - Siswa Bolos Mapel: 4/4 hadir umum (100%), tapi di MAT hanya 2/4 hadir (50% < 75%), sedangkan FIS 4/4 hadir (100%)
        for ($i = 0; $i < 4; $i++) {
            $tgl = $mulai->addDays($i)->toDateString();

            DailyAttendance::create(['student_id' => $siswaAman->id, 'tanggal' => $tgl, 'status' => 'hadir']);
            DailyAttendance::create(['student_id' => $siswaUmumRendah->id, 'tanggal' => $tgl, 'status' => $i < 2 ? 'hadir' : 'alpa']);
            DailyAttendance::create(['student_id' => $siswaMapelRendah->id, 'tanggal' => $tgl, 'status' => 'hadir']);

            SubjectAttendance::create(['student_id' => $siswaAman->id, 'tanggal' => $tgl, 'schedule_id' => $jadwalMat->id, 'status' => 'hadir']);
            SubjectAttendance::create(['student_id' => $siswaMapelRendah->id, 'tanggal' => $tgl, 'schedule_id' => $jadwalMat->id, 'status' => $i < 2 ? 'hadir' : 'alpa']);
            SubjectAttendance::create(['student_id' => $siswaMapelRendah->id, 'tanggal' => $tgl, 'schedule_id' => $jadwalFis->id, 'status' => 'hadir']);
        }

        $peringatan = app(RekapService::class)->peringatanSemester($periode->mulai, $periode->selesai);

        // Siswa tanpa data & siswa aman tidak boleh masuk daftar berisiko
        $this->assertSame(2, $peringatan['statistik']['total_siswa_berisiko']);
        $this->assertSame(1, $peringatan['statistik']['jumlah_umum_rendah']);
        $this->assertSame(1, $peringatan['statistik']['jumlah_mapel_rendah']);

        $umumItem = $peringatan['umum']->first();
        $this->assertSame('Siswa Umum Rendah', $umumItem->siswa->nama);
        $this->assertSame(50.0, $umumItem->umum->persen);
        $this->assertSame(4, $umumItem->umum->butuh_hadir);
        $this->assertSame('rawan', $umumItem->umum->kategori);
        $this->assertNotNull($umumItem->wa_url);

        $mapelItem = $peringatan['mapel']->first();
        $this->assertSame('Siswa Bolos Mapel', $mapelItem->siswa->nama);
        $this->assertFalse($mapelItem->is_umum_rendah);
        $this->assertTrue($mapelItem->is_mapel_rendah);
        $this->assertCount(1, $mapelItem->mapel_rendah);
        $this->assertSame('Matematika', $mapelItem->mapel_rendah->first()->nama);
        $this->assertSame(50.0, $mapelItem->mapel_rendah->first()->persen);
        $this->assertSame(4, $mapelItem->mapel_rendah->first()->butuh_jp);

        // Pastikan tampil di halaman dashboard admin
        $response = $this->actingAs($this->wali)->get('/admin');
        $response->assertOk();
        $response->assertSee('Peringatan Kehadiran 1 Semester');
        $response->assertSee('Siswa Umum Rendah');
        $response->assertSee('Siswa Bolos Mapel');
        $response->assertDontSee('Siswa Belum Diinput');
    }
}
