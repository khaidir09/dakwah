<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function list()
    {
        // Daftar acara dirender oleh <livewire:list-event />, yang memfilter
        // sendiri lewat Event::publiclyVisible().
        return view('pages.user.events.list');
    }

    public function detail(string $event)
    {
        $acara = Event::with(['assembly', 'contributor', 'village', 'district', 'city', 'province'])
            ->findOrFail(Event::idFromRouteParam($event));

        // Cek visibilitas mendahului kanonikalisasi agar redirect tidak
        // membocorkan nama acara yang belum disetujui.
        abort_unless($acara->isVisibleTo(Auth::user()), 404);

        if ($event !== $acara->route_slug) {
            return redirect()->route('event-detail', $acara->route_slug, 301);
        }

        return view('pages.user.events.detail', ['event' => $acara]);
    }
}
