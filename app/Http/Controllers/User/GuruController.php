<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Teacher;

class GuruController extends Controller
{
    public function list()
    {
        $teachers = Teacher::all();

        return view('pages/user/guru/list', compact('teachers'));
    }

    public function detail(Teacher $teacher)
    {
        $teacher->load('contributor');
        $schedules = Schedule::with('teacher')->where('teacher_id', $teacher->id)->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")->get();

        return view('pages/user/guru/detail', compact('teacher', 'schedules'));
    }
}
