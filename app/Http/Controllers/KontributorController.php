<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\Event;
use App\Models\KontribusiXpSetting;
use App\Models\Schedule;
use App\Models\ScheduleNote;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Wirid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KontributorController extends Controller
{
    public function index()
    {
        $leaderboard = User::role('Kontributor')
            ->where('total_khidmah_points', '>', 0)
            ->orderByDesc('total_khidmah_points')
            ->take(10)
            ->get(['name', 'username', 'badge_title', 'total_khidmah_points']);

        $xpSettings = KontribusiXpSetting::orderByDesc('points')->get();

        return view('pages.kontributor.index', compact('leaderboard', 'xpSettings'));
    }

    public function daftar(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('Kontributor')) {
            return redirect()->route('kontributor.saya')
                ->with('info', 'Anda sudah terdaftar sebagai Kontributor.');
        }

        $missing = $this->missingProfileFields($user);

        if (! empty($missing)) {
            return redirect()->route('kontributor.index')
                ->with('error', 'Profil belum lengkap. Harap lengkapi: ' . implode(', ', $missing))
                ->with('missing_fields', $missing);
        }

        $user->assignRole('Kontributor');

        // Lengkapi data profil publik kontributor (idempoten — tidak menimpa nilai yang sudah ada)
        if (empty($user->username)) {
            $user->username = $user->generateUniqueUsername();
        }
        if (empty($user->kontributor_since)) {
            $user->kontributor_since = now();
        }
        $user->save();

        return redirect()->route('kontributor.saya')
            ->with('success', 'Selamat! Anda kini terdaftar sebagai Kontributor Syaikhuna.');
    }

    public function profil(string $username)
    {
        $kontributor = User::where('username', $username)
            ->whereNotNull('kontributor_since')
            ->firstOrFail();

        $assemblies = Assembly::where('user_id', $kontributor->id)
            ->with(['teacher', 'village', 'district', 'schedule'])
            ->publiclyVisible()
            ->latest()
            ->get();

        $teachers = Teacher::where('contributor_user_id', $kontributor->id)
            ->with('village')
            ->publiclyVisible()
            ->latest()
            ->get();

        $wirids = Wirid::where('contributor_user_id', $kontributor->id)
            ->publiclyVisible()
            ->latest()
            ->get();

        $notes = ScheduleNote::where('user_id', $kontributor->id)
            ->with('schedule.assembly')
            ->publiclyVisible()
            ->latest()
            ->get();

        $events = Event::where('user_id', $kontributor->id)
            ->with(['village', 'district'])
            ->publiclyVisible()
            ->latest()
            ->get();

        $schedules = Schedule::where('contributor_user_id', $kontributor->id)
            ->with(['teacher', 'assembly'])
            ->publiclyVisible()
            ->latest()
            ->get();

        $stats = [
            'majelis' => $assemblies->count(),
            'guru' => $teachers->count(),
            'amalan' => $wirids->count(),
            'catatan' => $notes->count(),
            'acara' => $events->count(),
            'jadwal' => $schedules->count(),
        ];

        return view('pages.kontributor.profil', compact('kontributor', 'assemblies', 'teachers', 'wirids', 'stats', 'notes', 'events', 'schedules'));
    }

    private function missingProfileFields(User $user): array
    {
        $labels = [
            'phone' => 'Nomor HP',
            'province_code' => 'Provinsi',
            'city_code' => 'Kota/Kabupaten',
            'district_code' => 'Kecamatan',
            'village_code' => 'Desa/Kelurahan',
        ];

        $missing = [];

        foreach ($labels as $field => $label) {
            if (empty($user->$field)) {
                $missing[] = $label;
            }
        }

        return $missing;
    }
}
