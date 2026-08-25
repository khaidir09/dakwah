<?php

namespace App\Services;

use App\Models\Schedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Mengubah aturan recurrence pada Schedule menjadi tanggal-tanggal konkret.
 *
 * Model Schedule hanya menyimpan aturan dan merender label untuk manusia
 * (`recurrence_label`); service inilah yang menghitung tanggalnya.
 */
class ScheduleOccurrenceService
{
    /**
     * Indeks Carbon dayOfWeek (0 = Minggu) ke nama hari yang dipakai kolom `hari`.
     */
    private const DAY_NAMES = [
        0 => 'Minggu',
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
    ];

    public function __construct(private readonly HijriConverter $hijri) {}

    /**
     * @param  Collection<int, Schedule>  $schedules
     * @return array<int, array{schedule: Schedule, date: CarbonImmutable}>
     */
    public function expand(Collection $schedules, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $from = $from->startOfDay();
        $to = $to->startOfDay();

        $occurrences = [];

        for ($date = $from; $date->lte($to); $date = $date->addDay()) {
            foreach ($schedules as $schedule) {
                if ($this->matches($schedule, $date)) {
                    $occurrences[] = ['schedule' => $schedule, 'date' => $date];
                }
            }
        }

        usort($occurrences, function (array $a, array $b) {
            return [$a['date']->toDateString(), $this->timeOf($a['schedule'])]
                <=> [$b['date']->toDateString(), $this->timeOf($b['schedule'])];
        });

        return $occurrences;
    }

    public function matches(Schedule $schedule, CarbonImmutable $date): bool
    {
        $type = $schedule->recurrence_type ?: 'weekly';

        if ($type === 'monthly_date') {
            return $schedule->day_of_month !== null && $date->day === $schedule->day_of_month;
        }

        if (! $this->matchesDayName($schedule, $date)) {
            return false;
        }

        return match ($type) {
            'monthly_weekday' => $this->matchesWeekOfMonth($date, $schedule->week_of_month),
            'semimonthly' => $this->matchesWeekOfMonth($date, $schedule->week_of_month)
                || $this->matchesWeekOfMonth($date, $schedule->week_of_month_secondary),
            'hijri_first_week' => $this->hijri->toHijri($date)['day'] <= 7,
            default => true,
        };
    }

    private function matchesDayName(Schedule $schedule, CarbonImmutable $date): bool
    {
        if (! $schedule->hari) {
            return false;
        }

        return mb_strtolower(trim($schedule->hari)) === mb_strtolower(self::DAY_NAMES[$date->dayOfWeek]);
    }

    private function matchesWeekOfMonth(CarbonImmutable $date, ?string $week): bool
    {
        if (! $week) {
            return false;
        }

        if ($week === 'last') {
            return $date->day > $date->daysInMonth - 7;
        }

        return intdiv($date->day - 1, 7) + 1 === (int) $week;
    }

    private function timeOf(Schedule $schedule): string
    {
        return $schedule->waktu ? CarbonImmutable::parse($schedule->waktu)->format('H:i') : '99:99';
    }
}
