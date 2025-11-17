<?php

namespace App\Http\Controllers\User;

use App\Models\Assembly;
use App\Http\Controllers\Controller;
use App\Models\Schedule;

class MajelisController extends Controller
{
    public function list()
    {
        $assemblies = Assembly::with('teacher')->withCount('schedule')->get();
        return view('pages/user/majelis/list', compact('assemblies'));
    }
    public function detail($id)
    {
        $assembly = Assembly::findOrFail($id);
        $schedules = Schedule::with('teacher')->where('assembly_id', $assembly->id)->get();
        return view('pages/user/majelis/detail', compact('assembly', 'schedules'));
    }
}
