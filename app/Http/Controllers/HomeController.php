<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Event;
use App\Models\Schedule;

class HomeController extends Controller
{
    public function index()
    {
        $events = Event::latest();
        $events->where('date', '>=', now());
        return view('pages/user/home', compact('events'));
    }
}
