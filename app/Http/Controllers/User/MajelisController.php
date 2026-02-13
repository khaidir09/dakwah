<?php

namespace App\Http\Controllers\User;

use App\Models\Assembly;
use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Services\HijriService;

class MajelisController extends Controller
{
    public function list()
    {
        $assemblies = Assembly::with('teacher')->withCount('schedule')->get();
        return view('pages/user/majelis/list', compact('assemblies'));
    }
    public function detail($id, HijriService $hijriService)
    {
        $assembly = Assembly::findOrFail($id);
        $schedules = Schedule::with('teacher')->where('assembly_id', $assembly->id)->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")->get();
        $upcomingEvents = $assembly->events()->where('date', '>=', now())->orderBy('date', 'asc')->take(5)->get();

        $isRamadhan = $hijriService->isRamadhan();

        return view('pages/user/majelis/detail', compact('assembly', 'schedules', 'upcomingEvents', 'isRamadhan'));
    }
}
