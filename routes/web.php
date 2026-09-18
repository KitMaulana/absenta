<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DailyAttendanceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\RekapController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectAttendanceController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik — tanpa login, hanya data agregat + nama & no absen
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'index'])->name('publik.index');
Route::get('/siswa/cari', [PublicController::class, 'cariSiswa'])->name('publik.cari');
Route::get('/siswa/{siswa}/ringkasan', [PublicController::class, 'ringkasanSiswa'])->name('publik.siswa');
Route::get('/data/grafik', [PublicController::class, 'grafik'])->name('publik.grafik');

/*
|--------------------------------------------------------------------------
| Autentikasi admin
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Admin — wali kelas, ketua kelas, sekretaris
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    // Absensi umum harian
    Route::get('absensi-umum', [DailyAttendanceController::class, 'index'])->name('absensi-umum.index');
    Route::post('absensi-umum', [DailyAttendanceController::class, 'store'])->name('absensi-umum.store');
    Route::post('absensi-umum/libur', [DailyAttendanceController::class, 'setLibur'])->name('absensi-umum.libur.set');
    Route::post('absensi-umum/batal-libur', [DailyAttendanceController::class, 'batalLibur'])->name('absensi-umum.batal-libur');

    // Absensi per mata pelajaran (ceklis per JP)
    Route::get('absensi-mapel', [SubjectAttendanceController::class, 'index'])->name('absensi-mapel.index');
    Route::post('absensi-mapel', [SubjectAttendanceController::class, 'store'])->name('absensi-mapel.store');
    Route::post('absensi-mapel/salin', [SubjectAttendanceController::class, 'salin'])->name('absensi-mapel.salin');

    // Rekap & laporan PDF
    Route::get('rekap/{jenis}', [RekapController::class, 'show'])->name('rekap');
    Route::get('rekap/{jenis}/pdf', [RekapController::class, 'pdf'])->name('rekap.pdf');

    // Master data — siswa
    Route::get('siswa/export', [StudentController::class, 'exportCsv'])->name('siswa.export');
    Route::get('siswa/import', [StudentController::class, 'importForm'])->name('siswa.import.form');
    Route::post('siswa/import', [StudentController::class, 'import'])->name('siswa.import');
    Route::delete('siswa/reset', [StudentController::class, 'resetTotal'])->name('siswa.reset');
    Route::patch('siswa/{siswa}/toggle', [StudentController::class, 'toggle'])->name('siswa.toggle');
    Route::resource('siswa', StudentController::class)->except(['show']);

    // Master data — mata pelajaran, jadwal, hari libur
    Route::resource('mapel', SubjectController::class)->except(['show']);
    Route::get('jadwal/template', [ScheduleController::class, 'downloadTemplate'])->name('jadwal.template');
    Route::get('jadwal/import', [ScheduleController::class, 'showImport'])->name('jadwal.import.form');
    Route::post('jadwal/import', [ScheduleController::class, 'importCsv'])->name('jadwal.import');
    Route::get('jadwal/tambah-massal', [ScheduleController::class, 'bulkCreate'])->name('jadwal.bulk.create');
    Route::post('jadwal/tambah-massal', [ScheduleController::class, 'bulkStore'])->name('jadwal.bulk.store');
    Route::post('jadwal/bulk-delete', [ScheduleController::class, 'bulkDestroy'])->name('jadwal.bulk.destroy');
    Route::resource('jadwal', ScheduleController::class)->except(['show']);
    Route::resource('libur', HolidayController::class)->only(['index', 'store', 'update', 'destroy']);

    /*
    | Khusus wali kelas (super admin)
    */
    Route::middleware('role:wali_kelas')->group(function () {
        Route::get('pengaturan', [SettingController::class, 'edit'])->name('pengaturan.edit');
        Route::put('pengaturan', [SettingController::class, 'update'])->name('pengaturan.update');

        Route::patch('admins/{admin}/toggle', [AdminUserController::class, 'toggle'])->name('admins.toggle');
        Route::post('admins/{admin}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admins.reset');
        Route::resource('admins', AdminUserController::class)->except(['show'])->parameters(['admins' => 'admin']);
    });
});
