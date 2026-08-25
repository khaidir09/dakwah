<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use IntlCalendar;

/**
 * Konversi Hijriah <-> Masehi memakai kalender `islamic-civil` (aritmatis, offline).
 *
 * Sengaja memakai varian yang sama dengan parameter `m=islamic-civil` pada
 * HijriService agar tanggal Hijriah di API tidak pernah berbeda dari yang
 * ditampilkan web. Konsistensi antar keduanya lebih penting daripada
 * kesesuaian dengan penetapan rukyat setempat.
 */
class HijriConverter
{
    public const TIMEZONE = 'Asia/Makassar';

    private const CALENDAR = 'en@calendar=islamic-civil';

    /**
     * @return array{day: int, month: int, year: int}
     */
    public function toHijri(CarbonImmutable $date): array
    {
        $calendar = $this->calendar();
        $calendar->setTime($date->startOfDay()->getTimestampMs());

        return [
            'day' => $calendar->get(IntlCalendar::FIELD_DAY_OF_MONTH),
            // IntlCalendar memakai bulan berbasis 0.
            'month' => $calendar->get(IntlCalendar::FIELD_MONTH) + 1,
            'year' => $calendar->get(IntlCalendar::FIELD_YEAR),
        ];
    }

    /**
     * Tanggal yang melampaui panjang bulan Hijriah bersangkutan digeser ke
     * hari terakhir bulan itu (mis. 30 Zulhijjah pada tahun berumur 29 hari).
     */
    public function toGregorian(int $day, int $month, int $year): CarbonImmutable
    {
        $calendar = $this->calendar();
        $calendar->clear();
        $calendar->set($year, $month - 1, 1);

        $day = min($day, $calendar->getActualMaximum(IntlCalendar::FIELD_DAY_OF_MONTH));
        $calendar->set(IntlCalendar::FIELD_DAY_OF_MONTH, $day);

        return CarbonImmutable::createFromTimestampMs($calendar->getTime(), self::TIMEZONE)->startOfDay();
    }

    public function today(): array
    {
        return $this->toHijri(CarbonImmutable::now(self::TIMEZONE));
    }

    private function calendar(): IntlCalendar
    {
        return IntlCalendar::createInstance(self::TIMEZONE, self::CALENDAR);
    }
}
