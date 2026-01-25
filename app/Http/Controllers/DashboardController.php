<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Assembly;
use App\Models\Schedule;
use App\Models\Wirid;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalAssemblies = Assembly::count();
        $totalTeachers = Teacher::count();
        $upcomingSchedules = Schedule::where('waktu', '>=', now())->count();
        $totalWirid = Wirid::count();

        return view('pages/dashboard/dashboard', compact(
            'totalUsers',
            'totalAssemblies',
            'totalTeachers',
            'upcomingSchedules',
            'totalWirid'
        ));
    }

    /**
     * Displays the analytics screen
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function analytics()
    {
        return view('pages/dashboard/analytics');
    }

    /**
     * Displays the fintech screen
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function fintech()
    {
        return view('pages/dashboard/fintech');
    }
}
