<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Services\OneSignalService;
use App\Services\ScheduleOccurrenceService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class JadwalRemind extends Command
{
    protected $signature = 'jadwal:remind {--subuh : Kirim pengingat malam ini untuk jadwal besok yang mulai sebelum 07:00}';

    protected $description = 'Mengirim push pengingat jadwal majelis kepada pengikut majelis';

    public const LEAD_MINUTES = 120;

    public const WINDOW_MINUTES = 15;

    /**
     * Pengingat 2 jam sebelum jadwal subuh akan jatuh dini hari, sehingga jadwal
     * sebelum jam ini diingatkan malam sebelumnya lewat opsi --subuh.
     */
    public const EARLY_CUTOFF = '07:00';

    /** Batas jumlah external_id per request OneSignal. */
    private const ONESIGNAL_BATCH = 2000;

    public function handle(ScheduleOccurrenceService $occurrences, OneSignalService $oneSignal): int
    {
        $now = CarbonImmutable::now();

        if ($this->option('subuh')) {
            $date = $now->addDay()->startOfDay();
            $inWindow = fn (string $time) => $time < self::EARLY_CUTOFF;
        } else {
            // Dibulatkan ke kelipatan 15 menit agar jendela tiap run bersambung
            // tanpa celah walau scheduler terlambat beberapa detik.
            $start = $now->setTime($now->hour, intdiv($now->minute, self::WINDOW_MINUTES) * self::WINDOW_MINUTES)
                ->addMinutes(self::LEAD_MINUTES);
            $end = $start->addMinutes(self::WINDOW_MINUTES);
            $date = $start->startOfDay();
            $inWindow = fn (string $time) => $time >= self::EARLY_CUTOFF
                && $time >= $start->format('H:i')
                && ($end->isSameDay($start) ? $time < $end->format('H:i') : true);
        }

        $due = $this->candidates()
            ->filter(fn (Schedule $schedule) => $inWindow($this->timeOf($schedule)) && $occurrences->matches($schedule, $date));

        $sent = 0;
        $failed = 0;

        foreach ($due as $schedule) {
            if (! Cache::add('jadwal-remind:'.$schedule->id.':'.$date->toDateString(), true, now()->addDays(2))) {
                continue;
            }

            $followerIds = $schedule->assembly->followers()->pluck('users.id');

            foreach ($followerIds->chunk(self::ONESIGNAL_BATCH) as $batch) {
                $result = $oneSignal->sendNotification(
                    $schedule->assembly->nama_majelis,
                    $this->message($schedule, $date->isSameDay($now)),
                    $batch->values()->all(),
                    route('jadwal-majelis-detail', $schedule->route_slug),
                );

                $result === false ? $failed++ : $sent++;
            }
        }

        $this->info("Pengingat terkirim: {$sent} request, gagal: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return Collection<int, Schedule>
     */
    private function candidates(): Collection
    {
        return Schedule::query()
            ->publiclyVisible()
            ->where('status', 'Aktif')
            ->whereHas('assembly', fn ($q) => $q->publiclyVisible()->whereHas('followers'))
            ->with(['assembly', 'teacher'])
            ->get();
    }

    private function message(Schedule $schedule, bool $today): string
    {
        $when = ($today ? 'hari ini' : 'besok').' pukul '.$this->timeOf($schedule).' WITA';
        $teacher = $schedule->teacher?->name;

        return $schedule->nama_jadwal.' '.$when.($teacher ? ' bersama '.$teacher : '').'.';
    }

    private function timeOf(Schedule $schedule): string
    {
        return CarbonImmutable::parse($schedule->waktu)->format('H:i');
    }
}
