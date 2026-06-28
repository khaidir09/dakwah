<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardSetting;
use Illuminate\Http\Request;

class RewardSettingController extends Controller
{
    public function index()
    {
        $setting = RewardSetting::current();

        return view('pages.admin.reward.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:0|max:100000000',
            'min_xp' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        $setting = RewardSetting::current();
        $setting->update($validated);

        return redirect()->route('admin.reward-settings.index')
            ->with('message', 'Pengaturan reward berhasil diperbarui.');
    }
}
