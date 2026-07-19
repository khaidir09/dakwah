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
        // Set zona waktu ke WITA (Makassar)
        $now = Carbon::now()->setTimezone('Asia/Makassar');

        // Format Masehi: "Rabu, 4 Februari 2026"
        $this->gregorianDate = $now->locale('id')->isoFormat('dddd, D MMMM Y');

        // Ambil data Hijriah
        $this->hijriDate = $this->fetchHijriDate();
    }

    public function fetchHijriDate()
    {
        // Cache berdasarkan tanggal hari ini (WITA) agar tidak request berulang kali
        $date = now()->setTimezone('Asia/Makassar')->format('Y-m-d');
        $key = 'hijri_date_' . $date;

        // Simpan di cache selama 24 jam (60 detik * 60 menit * 24 jam)
        return Cache::remember($key, 60 * 60 * 24, function () {

            $url = "https://api.myquran.com/v3/cal/today?adj=-1&tz=Asia%2FMakassar";

            try {
                // Menggunakan withoutVerifying() untuk bypass masalah SSL certificate
                $response = Http::withoutVerifying()
                    ->timeout(5)
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
            }

            return 'Tanggal Hijriah Tidak Tersedia';
        });
    }

    public function render()
    {
        return view('livewire.hijri-calendar');
    }
}
