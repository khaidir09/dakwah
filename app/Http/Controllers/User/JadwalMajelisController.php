<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Services\HijriService;

class JadwalMajelisController extends Controller
{
    public function list(HijriService $hijriService)
    {
        $schedules = Schedule::with('teacher')->orderByRaw("
            CASE hari
                WHEN 'Senin' THEN 1
                WHEN 'Selasa' THEN 2
                WHEN 'Rabu' THEN 3
                WHEN 'Kamis' THEN 4
                WHEN 'Jumat' THEN 5
                WHEN 'Sabtu' THEN 6
                WHEN 'Minggu' THEN 7
                ELSE 8
            END
        ")->get();

        $isRamadhan = $hijriService->isRamadhan();

        return view('pages/user/jadwal-majelis/list', compact('schedules', 'isRamadhan'));
    }

    public function detail($id)
    {
        $schedule = Schedule::with(['teacher', 'assembly'])->findOrFail($id);

        $notesQuery = $schedule->notes()->with('user')->latest();

        if (auth()->check()) {
            $notes = $notesQuery->where(function ($query) {
                $query->where('visibility', 'Public')->where('status', 'Approved')
                      ->orWhere('user_id', auth()->id());
            })->get();
        } else {
            $notes = $notesQuery->where('visibility', 'Public')->where('status', 'Approved')->get();
        }

        return view('pages/user/jadwal-majelis/detail', compact('schedule', 'notes'));
    }
}
