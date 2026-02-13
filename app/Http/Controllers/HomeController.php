<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Event;
use App\Models\Schedule;
use App\Services\HijriService;

class HomeController extends Controller
{
    public function index(HijriService $hijriService)
    {
        $events = Event::latest();
        $events->where('date', '>=', now());

        $isRamadhan = $hijriService->isRamadhan();

        return view('pages/user/home', compact('events', 'isRamadhan'));
    }
}
