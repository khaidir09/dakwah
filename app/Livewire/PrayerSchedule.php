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
            $cityCode = $user->city_code;

            // Try to get mapped ID from cache
            $cacheKey = "myquran_id_{$cityCode}";
            $mappedId = Cache::get($cacheKey);

            if ($mappedId) {
                return $mappedId;
            }

            // If not cached, search via API
            try {
                $cityName = optional($user->city)->name;

                if ($cityName) {
                    // Search API
                    $response = Http::timeout(5)->get("https://api.myquran.com/v3/sholat/kota/cari/" . urlencode($cityName));

                    if ($response->successful()) {
                        $data = $response->json('data');
                        // Take the first result if available
                        if (!empty($data) && is_array($data) && isset($data[0]['id'])) {
                            $mappedId = $data[0]['id'];
                            // Cache for 30 days as city IDs rarely change
                            Cache::put($cacheKey, $mappedId, 60 * 60 * 24 * 30);
                            return $mappedId;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Fallback to default on error
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
        $prayerTimes = ['imsak', 'subuh', 'terbit', 'dhuha', 'dzuhur', 'ashar', 'maghrib', 'isya'];

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
