<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class PrayerSchedule extends Component
{
    public $schedule;
    public $location;

    public function mount()
    {
        $this->fetchPrayerSchedule();
    }

    public function fetchPrayerSchedule()
    {
        // Default Jakarta ID: 1301
        $cityId = 1301;
        $date = now();
        $cacheKey = "prayer_schedule_{$cityId}_" . $date->format('Y-m-d');

        // Try to get from cache first
        $data = Cache::get($cacheKey);

        if (! $data) {
            $year = $date->format('Y');
            $month = $date->format('m');
            $day = $date->format('d');

            try {
                $response = Http::timeout(5)->get("https://api.myquran.com/v2/sholat/jadwal/{$cityId}/{$year}/{$month}/{$day}");

                if ($response->successful()) {
                    $data = $response->json('data');
                    // Cache for 24 hours only if successful
                    Cache::put($cacheKey, $data, 60 * 60 * 24);
                }
            } catch (\Exception $e) {
                // Log error or handle silently, data remains null
            }
        }

        if ($data) {
            $this->location = $data['lokasi'] ?? 'Jakarta';
            $this->schedule = $data['jadwal'] ?? null;
        }
    }

    public function render()
    {
        return view('livewire.prayer-schedule');
    }
}
