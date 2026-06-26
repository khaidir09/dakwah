<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class KontributorController extends Controller
{
    public function index()
    {
        $leaderboard = User::role('Kontributor')
            ->where('total_khidmah_points', '>', 0)
            ->orderByDesc('total_khidmah_points')
            ->take(10)
            ->get(['name', 'badge_title', 'total_khidmah_points']);

        return view('pages.kontributor.index', compact('leaderboard'));
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

        return redirect()->route('kontributor.saya')
            ->with('success', 'Selamat! Anda kini terdaftar sebagai Kontributor Syaikhuna.');
    }

    private function missingProfileFields(User $user): array
    {
        $labels = [
            'phone'         => 'Nomor HP',
            'province_code' => 'Provinsi',
            'city_code'     => 'Kota/Kabupaten',
            'district_code' => 'Kecamatan',
            'village_code'  => 'Desa/Kelurahan',
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
