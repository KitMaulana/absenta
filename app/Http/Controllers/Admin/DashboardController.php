<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Setting;
use App\Models\SubjectAttendance;
use App\Services\RekapService;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(RekapService $rekap): View
    {
        $hariIni = CarbonImmutable::today();
        $awalBulan = $hariIni->startOfMonth();

        $hari = Schedule::hariDari($hariIni);
        $jadwalHariIni = Schedule::with('subject')->where('hari', $hari)->orderBy('jam_ke')->get();

        $jpTerisi = SubjectAttendance::where('tanggal', $hariIni->toDateString())
            ->distinct()
            ->pluck('schedule_id')
            ->flip();

        $tren = $rekap->trenHarian($hariIni->subDays(29), $hariIni);

        return view('admin.dashboard', [
            'hariIni' => $hariIni,
            'ringkasan' => $rekap->ringkasanHarian($hariIni),
            'bulanIni' => $rekap->ringkasanPeriode($awalBulan, $hariIni),
            'tren' => $tren,
            'rawanAlpa' => $rekap->siswaRawanAlpa($awalBulan, $hariIni),
            'jadwalHariIni' => $jadwalHariIni,
            'jpTerisi' => $jpTerisi,
            'hariAktif' => in_array($hari, Setting::hariAktif(), true),
            'namaHari' => Schedule::HARI[$hari],
            'perMapel' => $rekap->persenPerMapel($awalBulan, $hariIni),
        ]);
    }
}
