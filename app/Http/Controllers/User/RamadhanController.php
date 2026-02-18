<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RamadhanSchedule;
use Illuminate\Http\Request;

class RamadhanController extends Controller
{
    public function index()
    {
        $schedules = RamadhanSchedule::with(['assembly', 'assembly.city', 'assembly.district'])
            ->where('is_active', true)
            ->latest()
            ->paginate(12);

        return view('pages.user.ramadhan.index', compact('schedules'));
    }

    public function detail($id)
    {
        $schedule = RamadhanSchedule::with(['assembly', 'assembly.city', 'lectures' => function($query) {
            $query->orderBy('day');
        }, 'lectures.teacher'])
            ->where('is_active', true)
            ->findOrFail($id);

        return view('pages.user.ramadhan.detail', compact('schedule'));
    }
}
