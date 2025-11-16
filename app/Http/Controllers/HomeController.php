<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Schedule;

class HomeController extends Controller
{
    public function index()
    {
        $mapHari = [
            0 => 'Minggu',   // Carbon::SUNDAY
            1 => 'Senin',  // Carbon::MONDAY
            2 => 'Selasa', // Carbon::TUESDAY
            3 => 'Rabu',   // Carbon::WEDNESDAY
            4 => 'Kamis',  // Carbon::THURSDAY
            5 => 'Jumat',  // Carbon::FRIDAY
            6 => 'Sabtu',  // Carbon::SATURDAY
        ];
        // 3. Dapatkan hari ini sebagai angka (0 untuk Minggu, 1 untuk Senin, dst.)
        $hariIniAngka = Carbon::now()->dayOfWeek;

        // 4. Dapatkan nama hari yang sesuai dari array map
        $hariIni = $mapHari[$hariIniAngka];

        // 5. Ubah query untuk memfilter berdasarkan $hariIni
        $schedules = Schedule::with('teacher', 'assembly')
            ->where('hari', $hariIni)
            ->get();
        return view('pages/home', compact('schedules'));
    }
}
