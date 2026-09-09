<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.admins.index', [
            // CASE, bukan FIELD(), agar query tetap jalan di luar MySQL.
            'admins' => User::orderByRaw("CASE role WHEN 'wali_kelas' THEN 1 WHEN 'ketua_kelas' THEN 2 ELSE 3 END")
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.admins.form', ['admin' => new User(['role' => 'sekretaris', 'is_active' => true])]);
    }

    public function store(AdminUserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()->route('admin.admins.index')->with('sukses', 'Admin baru berhasil ditambahkan.');
    }

    public function edit(User $admin): View
    {
        return view('admin.admins.form', compact('admin'));
    }

    public function update(AdminUserRequest $request, User $admin): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        if ($this->akanMenghapusWaliTerakhir($admin, $data['role'] !== 'wali_kelas' || ! $data['is_active'])) {
            return back()->withInput()->with('gagal', 'Harus ada minimal satu wali kelas yang aktif.');
        }

        $admin->update($data);

        return redirect()->route('admin.admins.index')->with('sukses', 'Data admin berhasil diperbarui.');
    }

    public function toggle(User $admin): RedirectResponse
    {
        if ($admin->id === auth()->id()) {
            return back()->with('gagal', 'Anda tidak bisa menonaktifkan akun sendiri.');
        }

        if ($this->akanMenghapusWaliTerakhir($admin, $admin->is_active)) {
            return back()->with('gagal', 'Harus ada minimal satu wali kelas yang aktif.');
        }

        $admin->update(['is_active' => ! $admin->is_active]);

        return back()->with('sukses', sprintf(
            'Akun %s berhasil %s.', $admin->name, $admin->is_active ? 'diaktifkan' : 'dinonaktifkan',
        ));
    }

    public function resetPassword(User $admin): RedirectResponse
    {
        $baru = Str::password(10, symbols: false);
        $admin->update(['password' => $baru]);

        return back()->with('sukses', "Kata sandi {$admin->name} direset menjadi: {$baru} — catat sekarang, tidak ditampilkan lagi.");
    }

    public function destroy(Request $request, User $admin): RedirectResponse
    {
        if ($admin->id === auth()->id()) {
            return back()->with('gagal', 'Anda tidak bisa menghapus akun sendiri.');
        }

        if ($this->akanMenghapusWaliTerakhir($admin, true)) {
            return back()->with('gagal', 'Harus ada minimal satu wali kelas yang aktif.');
        }

        $admin->delete();

        return back()->with('sukses', 'Akun admin berhasil dihapus.');
    }

    /**
     * Benar jika $admin adalah satu-satunya wali kelas aktif dan perubahan
     * yang diminta akan mencabut status tersebut.
     */
    private function akanMenghapusWaliTerakhir(User $admin, bool $mencabut): bool
    {
        if (! $mencabut || ! $admin->isWaliKelas() || ! $admin->is_active) {
            return false;
        }

        return ! User::where('role', 'wali_kelas')
            ->where('is_active', true)
            ->whereKeyNot($admin->id)
            ->exists();
    }
}
