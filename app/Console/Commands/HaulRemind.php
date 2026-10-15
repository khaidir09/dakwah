<?php

namespace App\Console\Commands;

use App\Services\HaulService;
use App\Services\HijriConverter;
use App\Services\OneSignalService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class HaulRemind extends Command
{
    protected $signature = 'haul:remind';

    protected $description = 'Mengirim satu push ke semua pelanggan berisi haul guru hari ini dan besok';

    public function handle(HaulService $hauls, OneSignalService $oneSignal): int
    {
        $today = CarbonImmutable::now(HijriConverter::TIMEZONE)->startOfDay();

        $due = $hauls->upcoming($today)->filter(fn (array $row) => $row['days'] <= 1);

        if ($due->isEmpty()) {
            $this->info('Tidak ada haul hari ini atau besok.');

            return self::SUCCESS;
        }

        if (! Cache::add('haul-remind:'.$today->toDateString(), true, now()->addDays(2))) {
            $this->warn('Pengingat haul hari ini sudah dikirim.');

            return self::SUCCESS;
        }

        $names = fn (int $days) => $due->where('days', $days)->map(fn (array $row) => $row['teacher']->name)->join(', ');

        $message = collect(['Hari ini' => $names(0), 'Besok' => $names(1)])
            ->filter()
            ->map(fn (string $list, string $label) => $label.': '.$list.'.')
            ->push('Mari hadiahkan Al-Fatihah untuk beliau.')
            ->join(' ');

        // Satu guru langsung ke halamannya; lebih dari satu ke beranda yang memuat daftar haul.
        $url = $due->count() === 1 ? url('/guru/'.$due->first()['teacher']->slug) : url('/');

        if ($oneSignal->sendToAll('Haul Guru', $message, $url) === false) {
            Cache::forget('haul-remind:'.$today->toDateString());
            $this->error('Gagal mengirim pengingat haul.');

            return self::FAILURE;
        }

        $this->info('Pengingat haul terkirim: '.$message);

        return self::SUCCESS;
    }
}
