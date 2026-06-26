<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KontribusiXpSetting;
use Illuminate\Http\Request;

class XpSettingController extends Controller
{
    public function index()
    {
        $settings = KontribusiXpSetting::all()->keyBy('contribution_type');

        return view('pages.admin.xp-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'xp'           => 'required|array',
            'xp.*'         => 'required|integer|min:1|max:1000',
        ]);

        foreach ($validated['xp'] as $type => $points) {
            KontribusiXpSetting::where('contribution_type', $type)
                ->update(['points' => $points]);
        }

        return redirect()->route('admin.xp-settings.index')
            ->with('message', 'Pengaturan XP berhasil diperbarui.');
    }
}
