<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HakAksesTest extends TestCase
{
    use RefreshDatabase;

    private function buat(string $role, bool $aktif = true, string $email = null): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $email ?? $role.'@test.id',
            'password' => 'password',
            'role' => $role,
            'is_active' => $aktif,
        ]);
    }

    public function test_wali_kelas_boleh_membuka_manajemen_admin(): void
    {
        $this->actingAs($this->buat('wali_kelas'))
            ->get('/admin/admins')
            ->assertOk();
    }

    public function test_ketua_kelas_ditolak_di_manajemen_admin(): void
    {
        $this->actingAs($this->buat('ketua_kelas'))
            ->get('/admin/admins')
            ->assertForbidden();
    }

    public function test_sekretaris_ditolak_di_pengaturan_kelas(): void
    {
        $this->actingAs($this->buat('sekretaris'))
            ->get('/admin/pengaturan')
            ->assertForbidden();
    }

    public function test_ketua_kelas_boleh_input_absensi(): void
    {
        $this->actingAs($this->buat('ketua_kelas'))
            ->get('/admin/absensi-umum')
            ->assertOk();
    }

    public function test_akun_nonaktif_tidak_bisa_masuk(): void
    {
        $this->buat('ketua_kelas', aktif: false);

        $this->post('/login', ['email' => 'ketua_kelas@test.id', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_akun_yang_dinonaktifkan_saat_sesi_berjalan_akan_dikeluarkan(): void
    {
        $user = $this->buat('ketua_kelas');
        $this->actingAs($user);

        $user->update(['is_active' => false]);

        $this->get('/admin')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_wali_tidak_bisa_menonaktifkan_akun_sendiri(): void
    {
        $wali = $this->buat('wali_kelas');

        $this->actingAs($wali)
            ->patch("/admin/admins/{$wali->id}/toggle")
            ->assertSessionHas('gagal', 'Anda tidak bisa menonaktifkan akun sendiri.');

        $this->assertTrue($wali->fresh()->is_active);
    }

    public function test_wali_tidak_bisa_menghapus_akun_sendiri(): void
    {
        $wali = $this->buat('wali_kelas');

        $this->actingAs($wali)
            ->delete("/admin/admins/{$wali->id}")
            ->assertSessionHas('gagal', 'Anda tidak bisa menghapus akun sendiri.');

        $this->assertDatabaseHas('users', ['id' => $wali->id]);
    }

    /**
     * Form edit adalah satu-satunya jalur yang bisa mencabut status wali kelas
     * dari akun sendiri, jadi di sinilah penjagaan "minimal satu wali" diuji.
     */
    public function test_wali_terakhir_tidak_bisa_menurunkan_perannya_sendiri(): void
    {
        $wali = $this->buat('wali_kelas');
        $this->buat('ketua_kelas');

        $this->actingAs($wali)
            ->put("/admin/admins/{$wali->id}", [
                'name' => $wali->name,
                'email' => $wali->email,
                'role' => 'sekretaris',
                'is_active' => '1',
            ])
            ->assertSessionHas('gagal', 'Harus ada minimal satu wali kelas yang aktif.');

        $this->assertSame('wali_kelas', $wali->fresh()->role);
    }

    public function test_wali_terakhir_tidak_bisa_menonaktifkan_diri_lewat_form(): void
    {
        $wali = $this->buat('wali_kelas');

        $this->actingAs($wali)
            ->put("/admin/admins/{$wali->id}", [
                'name' => $wali->name,
                'email' => $wali->email,
                'role' => 'wali_kelas',
                // is_active tidak dikirim = checkbox tidak dicentang
            ])
            ->assertSessionHas('gagal', 'Harus ada minimal satu wali kelas yang aktif.');

        $this->assertTrue($wali->fresh()->is_active);
    }

    public function test_penurunan_peran_diizinkan_bila_masih_ada_wali_lain(): void
    {
        $waliA = $this->buat('wali_kelas');
        $waliB = $this->buat('wali_kelas', email: 'walib@test.id');

        $this->actingAs($waliA)
            ->put("/admin/admins/{$waliB->id}", [
                'name' => $waliB->name,
                'email' => $waliB->email,
                'role' => 'sekretaris',
                'is_active' => '1',
            ])
            ->assertSessionHas('sukses');

        $this->assertSame('sekretaris', $waliB->fresh()->role);
    }

    public function test_menambah_admin_baru(): void
    {
        $this->actingAs($this->buat('wali_kelas'))
            ->post('/admin/admins', [
                'name' => 'Sekretaris Baru',
                'email' => 'baru@test.id',
                'role' => 'sekretaris',
                'is_active' => '1',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
            ])
            ->assertSessionHas('sukses');

        $this->assertDatabaseHas('users', ['email' => 'baru@test.id', 'role' => 'sekretaris']);
    }

    public function test_password_admin_baru_wajib_dikonfirmasi(): void
    {
        $this->actingAs($this->buat('wali_kelas'))
            ->post('/admin/admins', [
                'name' => 'Salah Ketik',
                'email' => 'salah@test.id',
                'role' => 'sekretaris',
                'password' => 'rahasia123',
                'password_confirmation' => 'beda12345',
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'salah@test.id']);
    }
}
