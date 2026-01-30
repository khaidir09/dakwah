<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DailySurahReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DailySurah extends Component
{
    public function render()
    {
        $today = strtolower(Carbon::now()->format('l')); // monday, tuesday...

        // Ensure day name matches seed data (english)
        $readings = DailySurahReading::where('day', $today)->get();

        // Translate day to Indonesian for display
        $dayNameIndo = Carbon::now()->locale('id')->isoFormat('dddd');

        return view('livewire.daily-surah', [
            'readings' => $readings,
            'dayName' => $dayNameIndo
        ]);
    }
}
