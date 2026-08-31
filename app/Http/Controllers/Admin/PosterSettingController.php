<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosterSetting;
use Illuminate\Http\Request;

class PosterSettingController extends Controller
{
    public function index()
    {
        $setting = PosterSetting::current();

        return view('pages.admin.poster.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'monthly_quota' => 'required|integer|min:0|max:1000',
            'is_active' => 'required|boolean',
        ]);

        $setting = PosterSetting::current();
        $setting->update($validated);

        return redirect()->route('admin.poster-settings.index')
            ->with('message', 'Pengaturan poster AI berhasil diperbarui.');
    }
}
