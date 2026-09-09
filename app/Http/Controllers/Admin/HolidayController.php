<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HolidayRequest;
use App\Models\Holiday;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HolidayController extends Controller
{
    public function index(): View
    {
        return view('admin.libur.index', [
            'libur' => Holiday::orderByDesc('tanggal')->paginate(30),
        ]);
    }

    public function store(HolidayRequest $request): RedirectResponse
    {
        Holiday::create($request->validated());

        return back()->with('sukses', 'Hari libur berhasil ditambahkan.');
    }

    public function update(HolidayRequest $request, Holiday $libur): RedirectResponse
    {
        $libur->update($request->validated());

        return back()->with('sukses', 'Hari libur berhasil diperbarui.');
    }

    public function destroy(Holiday $libur): RedirectResponse
    {
        $libur->delete();

        return back()->with('sukses', 'Hari libur berhasil dihapus.');
    }
}
