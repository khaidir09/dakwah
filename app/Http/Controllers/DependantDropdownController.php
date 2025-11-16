<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;

class DependantDropdownController extends Controller
{
    public function cities(Request $request)
    {
        $cities = City::where('province_code', $request->get('code'))->pluck('name', 'code');

        return response()->json($cities);
    }

    public function districts(Request $request)
    {
        $districts = District::where('city_code', $request->get('code'))->pluck('name', 'code');

        return response()->json($districts);
    }

    public function villages(Request $request)
    {
        return \Indonesia::findDistrict($request->id, ['villages'])->villages->pluck('name', 'id');
    }
}
