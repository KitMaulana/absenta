<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleRequest;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        return view('admin.jadwal.index', [
            'perHari' => Schedule::with('subject')->orderBy('jam_ke')->get()->groupBy('hari'),
            'hariAktif' => Setting::hariAktif(),
        ]);
    }

    public function create(): View
    {
        return view('admin.jadwal.form', [
            'item' => new Schedule(['hari' => 'senin']),
            'mapel' => Subject::aktif()->orderBy('nama')->get(),
        ]);
    }

    public function store(ScheduleRequest $request): RedirectResponse
    {
        Schedule::create($request->validated());

        return redirect()->route('admin.jadwal.index')->with('sukses', 'Jadwal berhasil ditambahkan.');
    }

    public function edit(Schedule $jadwal): View
    {
        return view('admin.jadwal.form', [
            'item' => $jadwal,
            'mapel' => Subject::aktif()->orderBy('nama')->get(),
        ]);
    }

    public function update(ScheduleRequest $request, Schedule $jadwal): RedirectResponse
    {
        $jadwal->update($request->validated());

        return redirect()->route('admin.jadwal.index')->with('sukses', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Schedule $jadwal): RedirectResponse
    {
        if ($jadwal->subjectAttendances()->exists()) {
            return back()->with('gagal', 'Jadwal ini sudah punya riwayat absensi dan tidak bisa dihapus.');
        }

        $jadwal->delete();

        return back()->with('sukses', 'Jadwal berhasil dihapus.');
    }
}
