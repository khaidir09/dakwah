<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Teacher;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class HomeUpcomingHaul extends Component
{
    public function render()
    {
        $currentHijri = $this->fetchHijriDate();
        $upcomingHauls = collect();

        if ($currentHijri && $currentHijri !== 'Tanggal Hijriah Tidak Tersedia') {
            $parsed = $this->parseHijriDate($currentHijri);
            if ($parsed) {
                $month = $parsed['month'];
                $day = $parsed['day'];

                // Query 1: Remaining days in Current Month
                $currentMonthHauls = Teacher::where('wafat_hijriah_month', $month)
                    ->where('wafat_hijriah_day', '>=', $day)
                    ->orderBy('wafat_hijriah_day', 'asc')
                    ->get();

                // Query 2: Full Next Month
                // Handle Wrap Around (12 -> 1)
                $nextMonth = ($month == 12) ? 1 : $month + 1;
                
                $nextMonthHauls = Teacher::where('wafat_hijriah_month', $nextMonth)
                    ->orderBy('wafat_hijriah_day', 'asc')
                    ->get();

                // Merge: Current Month first, then Next Month
                $upcomingHauls = $currentMonthHauls->merge($nextMonthHauls);

                // Limit to 5 items
                $upcomingHauls = $upcomingHauls->take(5);
            }
        }

        return view('livewire.home-upcoming-haul', [
            'upcomingHauls' => $upcomingHauls
        ]);
    }

    private function parseHijriDate($hijriStr)
    {
        // Example: "17 Syakban 1447 H"
        // Remove Year (4 digits) and "H"
        $clean = trim(preg_replace('/(\d{4}|H)/i', '', $hijriStr));
        // Result: "17 Syakban " or "17 Rabiul Awal "

        if (preg_match('/^(\d+)\s+(.+)$/', trim($clean), $matches)) {
            $day = (int)$matches[1];
            $monthStr = trim($matches[2]);
            
            $monthNum = $this->getMonthNumber(strtolower($monthStr));
            
            if ($monthNum) {
                return ['day' => $day, 'month' => $monthNum];
            }
        }
        return null;
    }

    private function getMonthNumber($name)
    {
        $months = [
            'muharram' => 1,
            'safar' => 2,
            'rabiul awal' => 3,
            'rabiul akhir' => 4,
            'jumadil awal' => 5,
            'jumadil akhir' => 6,
            'rajab' => 7,
            'syakban' => 8,
            'ramadhan' => 9,
            'syawal' => 10,
            'zulkaidah' => 11,
            'zulhijah' => 12,
        ];
        
        // Exact match first
        if (isset($months[$name])) {
            return $months[$name];
        }

        // Partial match
        foreach ($months as $key => $val) {
             if (str_contains($name, $key)) {
                 return $val;
             }
        }
        return null;
    }

    public function fetchHijriDate()
    {
        $date = now()->setTimezone('Asia/Makassar')->format('Y-m-d');
        $key = 'hijri_date_' . $date;

        return Cache::remember($key, 60 * 60 * 24, function () {
            $url = "https://api.myquran.com/v3/cal/today?adj=-1&tz=Asia%2FMakassar";
            try {
                $response = Http::timeout(5)->get($url);
                if ($response->successful()) {
                    $data = $response->json('data');
                    if (isset($data['hijr']['today'])) {
                        $fullDate = $data['hijr']['today'];
                        $parts = explode(',', $fullDate);
                        if (count($parts) > 1) {
                            return trim($parts[1]); 
                        }
                        return $fullDate;
                    }
                }
            } catch (\Exception $e) {}
            return 'Tanggal Hijriah Tidak Tersedia';
        });
    }
}
