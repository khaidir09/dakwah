<?php

namespace App\Services;

use App\Models\Teacher;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Menghitung haul berikutnya untuk guru yang sudah wafat, berdasarkan tanggal
 * wafat Hijriah yang dikonversi ke Masehi dengan kalender islamic-civil.
 */
class HaulService
{
    public function __construct(private readonly HijriConverter $hijri) {}

    /**
     * @return Collection<int, array{teacher: Teacher, next: CarbonImmutable, days: int}>
     */
    public function upcoming(CarbonImmutable $today, ?int $limit = null): Collection
    {
        $hijriToday = $this->hijri->toHijri($today);

        $teachers = Teacher::query()
            ->publiclyVisible()
            ->whereNotNull('wafat_hijriah_year')
            ->whereNotNull('wafat_hijriah_day')
            ->whereNotNull('wafat_hijriah_month')
            ->get();

        $upcoming = $teachers
            ->map(function (Teacher $teacher) use ($today, $hijriToday) {
                $next = $this->hijri->toGregorian(
                    $teacher->wafat_hijriah_day,
                    $teacher->wafat_hijriah_month,
                    $hijriToday['year']
                );

                // Haul tahun ini sudah lewat -> pakai tahun Hijriah berikutnya.
                if ($next->lt($today)) {
                    $next = $this->hijri->toGregorian(
                        $teacher->wafat_hijriah_day,
                        $teacher->wafat_hijriah_month,
                        $hijriToday['year'] + 1
                    );
                }

                return [
                    'teacher' => $teacher,
                    'next' => $next,
                    'days' => (int) $today->diffInDays($next, false),
                ];
            })
            ->sortBy('days');

        return ($limit ? $upcoming->take($limit) : $upcoming)->values();
    }
}
