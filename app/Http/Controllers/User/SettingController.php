<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Laravolt\Indonesia\Models\Province;

class SettingController extends Controller
{
    public function index()
    {
        $provinces = Province::pluck('name', 'code');
        return view('pages.user.account', compact('provinces'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'photo' => ['nullable', 'image', 'max:1024'], // 1MB Max
            'province_code' => ['nullable', 'exists:indonesia_provinces,code'],
            'city_code' => ['nullable', 'exists:indonesia_cities,code'],
            'district_code' => ['nullable', 'exists:indonesia_districts,code'],
            'village_code' => ['nullable', 'exists:indonesia_villages,code'],
        ]);

        $user->forceFill([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $user->forceFill([
                'password' => Hash::make($request->password),
            ]);
        }

        // Handle Profile Photo
        if ($request->hasFile('photo')) {
            $user->updateProfilePhoto($request->file('photo'));
        }

        // Handle Regions
        $user->forceFill([
            'province_code' => $request->province_code,
            'city_code' => $request->city_code,
            'district_code' => $request->district_code,
            'village_code' => $request->village_code,
        ])->save();

        return back()->with('status', 'profile-updated');
    }
}
