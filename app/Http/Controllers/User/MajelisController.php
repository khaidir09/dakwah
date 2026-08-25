<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Assembly;
use App\Models\Schedule;
use App\Services\HijriService;
use Illuminate\Support\Facades\Auth;

class MajelisController extends Controller
{
    public function list()
    {
        // Daftar majelis dirender oleh <livewire:list-majelis />, yang memfilter
        // sendiri lewat Assembly::publiclyVisible().
        return view('pages/user/majelis/list');
    }

    public function detail(string $majelis, HijriService $hijriService)
    {
        $assembly = Assembly::with('contributor')->findOrFail(Assembly::idFromRouteParam($majelis));

        // Cek visibilitas mendahului kanonikalisasi agar redirect tidak
        // membocorkan nama majelis yang belum disetujui.
        abort_unless($assembly->isVisibleTo(Auth::user()), 404);

        if ($majelis !== $assembly->route_slug) {
            return redirect()->route('majelis-detail', $assembly->route_slug, 301);
        }

        // CASE, bukan FIELD(): FIELD hanya ada di MySQL sehingga halaman ini tidak bisa diuji.
        $urutanHari = "CASE hari
            WHEN 'Senin' THEN 1
            WHEN 'Selasa' THEN 2
            WHEN 'Rabu' THEN 3
            WHEN 'Kamis' THEN 4
            WHEN 'Jumat' THEN 5
            WHEN 'Sabtu' THEN 6
            WHEN 'Minggu' THEN 7
            ELSE 8 END";

        $schedules = Schedule::with('teacher')
            ->publiclyVisible()
            ->where('assembly_id', $assembly->id)
            ->orderByRaw($urutanHari)
            ->get();

        $upcomingEvents = $assembly->events()
            ->publiclyVisible()
            ->where('date', '>=', now())
            ->orderBy('date', 'asc')
            ->take(5)
            ->get();

        $isRamadhan = $hijriService->isRamadhan();

        return view('pages/user/majelis/detail', compact('assembly', 'schedules', 'upcomingEvents', 'isRamadhan'));
    }
}
