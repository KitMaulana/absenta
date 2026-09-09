<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubjectRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        return view('admin.mapel.index', [
            'mapel' => Subject::withCount('schedules')->orderBy('nama')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.mapel.form', ['item' => new Subject(['warna' => '#3b82f6', 'is_active' => true])]);
    }

    public function store(SubjectRequest $request): RedirectResponse
    {
        Subject::create($request->validated());

        return redirect()->route('admin.mapel.index')->with('sukses', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Subject $mapel): View
    {
        return view('admin.mapel.form', ['item' => $mapel]);
    }

    public function update(SubjectRequest $request, Subject $mapel): RedirectResponse
    {
        $mapel->update($request->validated());

        return redirect()->route('admin.mapel.index')->with('sukses', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $mapel): RedirectResponse
    {
        if ($mapel->schedules()->exists()) {
            return back()->with('gagal', 'Mata pelajaran ini masih dipakai di jadwal. Hapus jadwalnya dulu atau nonaktifkan mapel.');
        }

        $mapel->delete();

        return back()->with('sukses', 'Mata pelajaran berhasil dihapus.');
    }
}
