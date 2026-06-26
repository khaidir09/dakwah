<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assembly;
use App\Models\Event;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\Wirid;
use App\Services\KhidmahService;
use Illuminate\Http\Request;

class ModerasiController extends Controller
{
    public function __construct(protected KhidmahService $khidmah) {}

    public function moderasiAssembly(Request $request, int $id)
    {
        return $this->proses($request, Assembly::findOrFail($id), 'majelis', $id, 'majelis.index');
    }

    public function moderasiTeacher(Request $request, int $id)
    {
        return $this->proses($request, Teacher::findOrFail($id), 'guru', $id, 'guru.index');
    }

    public function moderasiJadwal(Request $request, int $id)
    {
        return $this->proses($request, Schedule::findOrFail($id), 'jadwal', $id, 'jadwal-majelis.index');
    }

    public function moderasiEvent(Request $request, int $id)
    {
        $event = Event::findOrFail($id);

        if ($request->input('aksi') === 'setujui') {
            $event->status = 'approved';
            $event->moderated_at = now();
            $event->rejection_reason = null;
            $event->save();

            if ($event->user_id) {
                $points = \App\Models\KontribusiXpSetting::pointsFor('acara');
                \App\Models\Contribution::where('contributable_id', $event->id)
                    ->where('contributable_type', Event::class)
                    ->where('user_id', $event->user_id)
                    ->update(['points_earned' => $points]);

                $user = \App\Models\User::find($event->user_id);
                if ($user) {
                    $oldBadge = $user->badge_title;
                    $user->increment('total_khidmah_points', $points);
                    $user->refresh();
                    $badgeChanged = $user->updateBadge();
                    $user->notify(new \App\Notifications\KontribusiDisetujui($event->name, $points));
                    if ($badgeChanged && $user->badge_title !== $oldBadge) {
                        $user->notify(new \App\Notifications\BadgeNaik($user->badge_title));
                    }
                }
            }

            return redirect()->route('event.index')->with('message', 'Acara disetujui.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:500']);
        $event->status = 'rejected';
        $event->rejection_reason = $request->rejection_reason;
        $event->moderated_at = now();
        $event->save();

        if ($event->user_id) {
            $user = \App\Models\User::find($event->user_id);
            $user?->notify(new \App\Notifications\KontribusiDitolak($event->name, $request->rejection_reason));
        }

        return redirect()->route('event.index')->with('message', 'Acara ditolak.');
    }

    public function moderasiWirid(Request $request, int $id)
    {
        return $this->proses($request, Wirid::findOrFail($id), 'amalan', $id, 'wirid.index');
    }

    public function revokeAssembly(Request $request, int $id)
    {
        return $this->revoke($request, Assembly::findOrFail($id), 'majelis', 'majelis.index');
    }

    public function revokeTeacher(Request $request, int $id)
    {
        return $this->revoke($request, Teacher::findOrFail($id), 'guru', 'guru.index');
    }

    public function revokeJadwal(Request $request, int $id)
    {
        return $this->revoke($request, Schedule::findOrFail($id), 'jadwal', 'jadwal-majelis.index');
    }

    public function revokeEvent(Request $request, int $id)
    {
        $request->validate(['rejection_reason' => 'required|string|max:500']);
        $event = Event::findOrFail($id);

        $contribution = \App\Models\Contribution::where('contributable_id', $event->id)
            ->where('contributable_type', Event::class)
            ->first();

        $pointsToDeduct = $contribution?->points_earned ?? 0;

        $event->status = 'rejected';
        $event->rejection_reason = $request->rejection_reason;
        $event->moderated_at = now();
        $event->save();

        if ($contribution) {
            $contribution->points_earned = 0;
            $contribution->save();
        }

        if ($event->user_id) {
            $user = \App\Models\User::find($event->user_id);
            if ($user && $pointsToDeduct > 0) {
                $user->total_khidmah_points = max(0, $user->total_khidmah_points - $pointsToDeduct);
                $user->save();
                $user->updateBadge();
            }
            $user?->notify(new \App\Notifications\KontribusiDitolak($event->name, $request->rejection_reason));
        }

        return redirect()->route('event.index')->with('message', 'Persetujuan acara dibatalkan.');
    }

    public function revokeWirid(Request $request, int $id)
    {
        return $this->revoke($request, Wirid::findOrFail($id), 'amalan', 'wirid.index');
    }

    private function proses(Request $request, $entity, string $type, int $id, string $redirectRoute)
    {
        $label = $this->getLabel($entity);

        if ($request->input('aksi') === 'setujui') {
            $this->khidmah->approve($entity, $type, $label);

            return redirect()->route($redirectRoute)->with('message', ucfirst($type) . ' disetujui.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:500']);
        $this->khidmah->reject($entity, $label, $request->rejection_reason);

        return redirect()->route($redirectRoute)->with('message', ucfirst($type) . ' ditolak.');
    }

    private function revoke(Request $request, $entity, string $type, string $redirectRoute)
    {
        $request->validate(['rejection_reason' => 'required|string|max:500']);
        $label = $this->getLabel($entity);
        $this->khidmah->revoke($entity, $type, $label, $request->rejection_reason);

        return redirect()->route($redirectRoute)->with('message', 'Persetujuan dibatalkan.');
    }

    private function getLabel($entity): string
    {
        return $entity->nama_majelis
            ?? $entity->name
            ?? $entity->nama_jadwal
            ?? $entity->nama
            ?? 'Data';
    }
}
