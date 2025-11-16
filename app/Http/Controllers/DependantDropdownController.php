<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Village;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;

class DependantDropdownController extends Controller
{
    public function provinces()
    {
        $provinces = Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');
        return view('pages.guru.create', compact('provinces'));
    }

    // --- PERBAIKAN DI SINI ---
    // Terima $province_code langsung dari URL
    public function getCities($province_code)
    {
        // Gunakan $province_code untuk query
        $cities = City::where('province_code', $province_code)->pluck('name', 'code');
        return response()->json($cities);
    }

    // Terima $city_code langsung dari URL
    public function getDistricts($city_code)
    {
        $districts = District::where('city_code', $city_code)->pluck('name', 'code');
        return response()->json($districts);
    }

    // Terima $district_code langsung dari URL
    public function getVillages($district_code)
    {
        $villages = Village::where('district_code', $district_code)->pluck('name', 'code');
        return response()->json($villages);
    }
}
