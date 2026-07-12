<?php

namespace App\Http\Controllers;

use App\Services\DashboardStatsService;

class DashboardController extends Controller
{
    public function index(DashboardStatsService $stats)
    {
        return view('pages/dashboard/dashboard', [
            'summary' => $stats->summary(),
            'queues' => $stats->moderationQueues(),
            'latestPending' => $stats->latestPending(),
        ]);
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
