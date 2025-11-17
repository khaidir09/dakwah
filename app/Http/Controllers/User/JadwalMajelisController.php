<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Schedule;

class JadwalMajelisController extends Controller
{
    public function list()
    {
        $schedules = Schedule::with('teacher')->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")->get();
        return view('pages/user/jadwal-majelis/list', compact('schedules'));
    }
}
