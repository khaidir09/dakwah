<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class HijriService
{
    /**
     * Get the current Hijri date string.
     * Example: "Rabu, 17 Syakban 1447 H" or "17 Syakban 1447 H"
     *
     * @return string
     */
    public function getHijriDateString()
    {
        // Cache berdasarkan tanggal hari ini (WITA) agar tidak request berulang kali
        $date = now()->setTimezone('Asia/Makassar')->format('Y-m-d');
        $key = 'hijri_date_' . $date;

        // Simpan di cache selama 24 jam (60 detik * 60 menit * 24 jam)
        return Cache::remember($key, 60 * 60 * 24, function () {

            $url = "https://api.myquran.com/v3/cal/today?adj=-1&tz=Asia%2FMakassar";

            try {
                // Hapus withoutVerifying() karena API supports SSL
                $response = Http::timeout(2)
                    ->get($url);

                if ($response->successful()) {
                    $data = $response->json('data');

                    // Struktur: data -> hijr -> today
                    if (isset($data['hijr']['today'])) {
                        $fullDate = $data['hijr']['today'];
                        // Contoh nilai asli: "Rabu, 17 Syakban 1447 H"

                        // Opsional: Menghapus nama hari agar tidak duplikat dengan tanggal Masehi
                        // Kita pecah string berdasarkan koma ","
                        $parts = explode(',', $fullDate);

                        // Jika berhasil dipecah, ambil bagian keduanya (tanggal saja)
                        if (count($parts) > 1) {
                            return trim($parts[1]); // Output: "17 Syakban 1447 H"
                        }

                        // Jika format berbeda, kembalikan apa adanya
                        return $fullDate;
                    }
                }
            } catch (\Exception $e) {
                // Log error if needed, but for now just return fallback
            }

            return 'Tanggal Hijriah Tidak Tersedia';
        });
    }

    /**
     * Check if the current Hijri date is in Ramadhan.
     *
     * @return bool
     */
    public function isRamadhan()
    {
        $hijriDate = $this->getHijriDateString();

        // Case-insensitive check for "Ramadhan" or "Ramadan"
        return str_contains(strtolower($hijriDate), 'ramadhan') || str_contains(strtolower($hijriDate), 'ramadan');
    }
}
