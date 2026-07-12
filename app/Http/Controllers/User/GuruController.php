<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

class GuruController extends Controller
{
    public function list()
    {
        $teachers = Teacher::all();

        return view('pages/user/guru/list', compact('teachers'));
    }

    public function detail(Teacher $teacher)
    {
        abort_unless($teacher->isVisibleTo(Auth::user()), 404);

        $teacher->load('contributor');
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
            ->where('teacher_id', $teacher->id)
            ->orderByRaw($urutanHari)
            ->get();

        return view('pages/user/guru/detail', compact('teacher', 'schedules'));
    }
}
