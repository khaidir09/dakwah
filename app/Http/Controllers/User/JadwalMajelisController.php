<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Services\HijriService;
use Illuminate\Support\Facades\Auth;

class JadwalMajelisController extends Controller
{
    public function list(HijriService $hijriService)
    {
        // Daftar jadwal dirender oleh <livewire:list-jadwal-majelis />, yang
        // memfilter sendiri lewat Schedule::publiclyVisible().
        $isRamadhan = $hijriService->isRamadhan();

        return view('pages/user/jadwal-majelis/list', compact('isRamadhan'));
    }

    public function detail(string $jadwal)
    {
        $schedule = Schedule::with(['teacher', 'assembly'])->findOrFail(Schedule::idFromRouteParam($jadwal));

        // Cek visibilitas mendahului kanonikalisasi agar redirect tidak
        // membocorkan nama jadwal yang belum disetujui.
        abort_unless($schedule->isVisibleTo(Auth::user()), 404);

        if ($jadwal !== $schedule->route_slug) {
            return redirect()->route('jadwal-majelis-detail', $schedule->route_slug, 301);
        }

        $notesQuery = $schedule->notes()->with(['user', 'comments.user'])->latest();

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
