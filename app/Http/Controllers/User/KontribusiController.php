<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Assembly;
use App\Models\Event;
use App\Models\RewardSetting;
use App\Models\Schedule;
use App\Models\ScheduleNote;
use App\Models\Teacher;
use App\Models\Wirid;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class KontribusiController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $majelis = Assembly::where('user_id', $user->id)
            ->whereNotNull('contribution_status')
            ->latest()
            ->get();

        $guru = Teacher::where('contributor_user_id', $user->id)
            ->latest()
            ->get();

        $jadwal = Schedule::where('contributor_user_id', $user->id)
            ->latest()
            ->get();

        $acara = Event::where('user_id', $user->id)
            ->whereNotNull('status')
            ->latest()
            ->paginate(10, ['*'], 'acara_page');

        $amalan = Wirid::where('contributor_user_id', $user->id)
            ->latest()
            ->get();

        $catatan = ScheduleNote::where('user_id', $user->id)
            ->whereNotNull('contribution_status')
            ->latest()
            ->get();

        $semua = collect()
            ->merge($majelis->map(fn ($m) => ['jenis' => 'Majelis', 'label' => $m->nama_majelis, 'status' => $m->contribution_status, 'alasan' => $m->rejection_reason, 'date' => $m->created_at, 'edit_route' => route('kontributor.majelis.edit', $m->id), 'xp' => $m->contribution()->value('points_earned') ?? 0]))
            ->merge($guru->map(fn ($g) => ['jenis' => 'Guru', 'label' => $g->name, 'status' => $g->contribution_status, 'alasan' => $g->rejection_reason, 'date' => $g->created_at, 'edit_route' => route('kontributor.guru.edit', $g->id), 'xp' => $g->contribution()->value('points_earned') ?? 0]))
            ->merge($jadwal->map(fn ($j) => ['jenis' => 'Jadwal', 'label' => $j->nama_jadwal, 'status' => $j->contribution_status, 'alasan' => $j->rejection_reason, 'date' => $j->created_at, 'edit_route' => route('kontributor.jadwal.edit', $j->id), 'xp' => $j->contribution()->value('points_earned') ?? 0]))
            ->merge($amalan->map(fn ($a) => ['jenis' => 'Amalan', 'label' => $a->nama, 'status' => $a->contribution_status, 'alasan' => $a->rejection_reason, 'date' => $a->created_at, 'edit_route' => route('kontributor.amalan.edit', $a->id), 'xp' => $a->contribution()->value('points_earned') ?? 0]))
            ->merge($catatan->map(fn ($c) => ['jenis' => 'Catatan Pengajian', 'label' => Str::limit(strip_tags($c->content), 60), 'status' => $c->contribution_status, 'alasan' => $c->rejection_reason, 'date' => $c->created_at, 'edit_route' => route('kelola-catatan.index'), 'xp' => $c->contribution()->value('points_earned') ?? 0]))
            ->sortByDesc('date')
            ->values();

        $rewardSetting = RewardSetting::current();
        $rewardEligible = $user->eligibleForReward();
        $latestClaim = $user->rewardClaims()->latest()->first();

        return view('pages.kontributor.saya', compact(
            'user', 'majelis', 'guru', 'jadwal', 'acara', 'amalan', 'catatan', 'semua',
            'rewardSetting', 'rewardEligible', 'latestClaim'
        ));
    }
}
