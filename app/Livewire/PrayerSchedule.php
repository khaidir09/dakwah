<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PrayerSchedule extends Component
{
    public $schedule;
    public $location;
    public $layout = 'sidebar'; // 'sidebar' or 'horizontal'

    public function mount($layout = 'sidebar')
    {
        $this->layout = $layout;
        $this->fetchPrayerSchedule();
    }

    private function resolveCityId()
    {
        $defaultCityId = '2f2b265625d76a6704b08093c652fd79'; // Hulu Sungai Utara

        if (Auth::check() && Auth::user()->city_code) {
            $user = Auth::user();
            $myQuranId = optional($user->city)->api_myquran;

            if ($myQuranId) {
                return $myQuranId;
            }
        }

        return $defaultCityId;
    }

    public function fetchPrayerSchedule()
    {
        // Default ID Hulu Sungai Utara or resolved from user
        $cityId = $this->resolveCityId();
        $date = now();
        $cacheKey = "prayer_schedule_{$cityId}_" . $date->format('Y-m-d');

        // Try to get from cache first
        $data = Cache::get($cacheKey);

        if (! $data) {
            try {
                $response = Http::timeout(5)->get("https://api.myquran.com/v3/sholat/jadwal/$cityId/today?utc=Asia/Makassar");

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
            $this->location = $data['lokasi'] ?? $data['kabko'] ?? 'Hulu Sungai Utara';
            $jadwal = $data['jadwal'] ?? null;

            if ($jadwal && is_array($jadwal)) {
                $firstKey = array_key_first($jadwal);
                if ($firstKey !== null && is_array($jadwal[$firstKey])) {
                    $this->schedule = $jadwal[$firstKey];
                } else {
                    $this->schedule = $jadwal;
                }
            } else {
                $this->schedule = null;
            }
        }
    }

    public function render()
    {
        $activePrayer = null;
        $nextPrayer = null;
        $prayerTimes = ['subuh', 'terbit', 'dzuhur', 'ashar', 'maghrib', 'isya'];

        if ($this->schedule) {
            $now = now()->setTimezone('Asia/Makassar')->format('H:i');

            foreach ($prayerTimes as $key) {
                $time = $this->schedule[$key] ?? '00:00';
                if ($now >= $time) {
                    $activePrayer = $key;
                } else {
                    $nextPrayer = $key;
                    break;
                }
            }
        }

        $view = $this->layout === 'horizontal'
            ? 'livewire.prayer-schedule-horizontal'
            : 'livewire.prayer-schedule';

        return view($view, [
            'activePrayer' => $activePrayer,
            'nextPrayer' => $nextPrayer,
            'prayerList' => $prayerTimes
        ]);
    }
}
