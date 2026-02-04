<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class HijriCalendar extends Component
{
    public $gregorianDate;
    public $hijriDate;

    public function mount()
    {
        // Format: Senin, 20 Mei 2024
        $this->gregorianDate = Carbon::now()->setTimezone('Asia/Jakarta')->locale('id')->isoFormat('dddd, D MMMM Y');
        $this->hijriDate = $this->fetchHijriDate();
    }

    public function fetchHijriDate()
    {
        // Cache based on today's date (Jakarta Time)
        $date = now()->setTimezone('Asia/Jakarta')->format('Y-m-d');
        $key = 'hijri_date_' . $date;

        return Cache::remember($key, 60 * 60 * 24, function () use ($date) {

            // Attempt to fetch from API Muslim v3/v2
            // Endpoint documentation implies: https://api.myquran.com/v3/doc#tag/Kalender
            // Common working endpoint for MyQuran:
            $url = "https://api.myquran.com/v2/cal/hijri/{$date}";

            try {
                $response = Http::timeout(5)->get($url);

                if ($response->successful()) {
                    $data = $response->json('data');
                    // Expected: "date_cal": "11 Dzulqaidah 1445"
                    if (isset($data['date_cal'])) {
                        return $data['date_cal'] . ' H';
                    }
                }
            } catch (\Exception $e) {
                // Silently fail to fallback
            }

            return 'Tanggal Hijriah Tidak Tersedia';
        });
    }

    public function render()
    {
        return view('livewire.hijri-calendar');
    }
}
