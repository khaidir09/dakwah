<?php

namespace Tests\Unit;

use App\Models\Schedule;
use App\Services\HijriConverter;
use App\Services\ScheduleOccurrenceService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class ScheduleOccurrenceServiceTest extends TestCase
{
    private ScheduleOccurrenceService $service;

    private HijriConverter $hijri;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hijri = new HijriConverter;
        $this->service = new ScheduleOccurrenceService($this->hijri);
    }

    private function schedule(array $attributes): Schedule
    {
        return new Schedule(array_merge([
            'nama_jadwal' => 'Kajian',
            'waktu' => '2026-01-01 19:30:00',
            'recurrence_type' => 'weekly',
        ], $attributes));
    }

    /**
     * @return array<int, string>
     */
    private function dates(Schedule $schedule, string $from, string $to): array
    {
        $occurrences = $this->service->expand(
            new Collection([$schedule]),
            CarbonImmutable::parse($from, HijriConverter::TIMEZONE),
            CarbonImmutable::parse($to, HijriConverter::TIMEZONE),
        );

        return array_map(fn (array $o) => $o['date']->toDateString(), $occurrences);
    }

    /** @test */
    public function weekly_yields_one_date_in_a_seven_day_window()
    {
        // 2026-07-22 adalah hari Rabu.
        $dates = $this->dates($this->schedule(['hari' => 'Rabu']), '2026-07-21', '2026-07-27');

        $this->assertSame(['2026-07-22'], $dates);
    }

    /** @test */
    public function weekly_repeats_every_seven_days_in_a_longer_window()
    {
        $dates = $this->dates($this->schedule(['hari' => 'Rabu']), '2026-07-21', '2026-08-03');

        $this->assertSame(['2026-07-22', '2026-07-29'], $dates);
    }

    /** @test */
    public function monthly_weekday_matches_only_the_configured_week()
    {
        $schedule = $this->schedule([
            'recurrence_type' => 'monthly_weekday',
            'hari' => 'Rabu',
            'week_of_month' => '2',
        ]);

        // Rabu di Juli 2026: 1, 8, 15, 22, 29. Pekan ke-2 = tanggal 8-14.
        $this->assertSame(['2026-07-08'], $this->dates($schedule, '2026-07-01', '2026-07-31'));
    }

    /** @test */
    public function last_week_of_month_works_across_month_lengths()
    {
        $schedule = $this->schedule([
            'recurrence_type' => 'monthly_weekday',
            'hari' => 'Sabtu',
            'week_of_month' => 'last',
        ]);

        // Februari 2027 = 28 hari, Sabtu terakhir 27; Juli 2026 = 31 hari, Sabtu terakhir 25.
        $this->assertSame(['2027-02-27'], $this->dates($schedule, '2027-02-01', '2027-02-28'));
        $this->assertSame(['2026-07-25'], $this->dates($schedule, '2026-07-01', '2026-07-31'));

        // Februari 2028 = 29 hari (kabisat Masehi).
        $this->assertSame(['2028-02-26'], $this->dates($schedule, '2028-02-01', '2028-02-29'));
    }

    /** @test */
    public function semimonthly_matches_both_configured_weeks()
    {
        $schedule = $this->schedule([
            'recurrence_type' => 'semimonthly',
            'hari' => 'Rabu',
            'week_of_month' => '1',
            'week_of_month_secondary' => '3',
        ]);

        // Pekan ke-1 = tanggal 1-7 (Rabu 1), pekan ke-3 = 15-21 (Rabu 15).
        $this->assertSame(['2026-07-01', '2026-07-15'], $this->dates($schedule, '2026-07-01', '2026-07-31'));
    }

    /** @test */
    public function monthly_date_matches_the_literal_day()
    {
        $schedule = $this->schedule(['recurrence_type' => 'monthly_date', 'day_of_month' => 10, 'hari' => null]);

        $this->assertSame(['2026-07-10'], $this->dates($schedule, '2026-07-01', '2026-07-31'));
    }

    /** @test */
    public function monthly_date_31_yields_nothing_in_a_30_day_month()
    {
        $schedule = $this->schedule(['recurrence_type' => 'monthly_date', 'day_of_month' => 31, 'hari' => null]);

        // Juni 2026 hanya 30 hari — tidak digeser ke tanggal 30.
        $this->assertSame([], $this->dates($schedule, '2026-06-01', '2026-06-30'));
    }

    /** @test */
    public function hijri_first_week_matches_only_hijri_days_one_to_seven()
    {
        $schedule = $this->schedule(['recurrence_type' => 'hijri_first_week', 'hari' => 'Jumat']);

        $dates = $this->dates($schedule, '2026-07-01', '2026-08-31');

        $this->assertNotEmpty($dates);

        foreach ($dates as $date) {
            $hijri = $this->hijri->toHijri(CarbonImmutable::parse($date, HijriConverter::TIMEZONE));

            $this->assertLessThanOrEqual(7, $hijri['day'], "Tanggal {$date} berada di luar pekan pertama Hijriah.");
            $this->assertSame('Jumat', CarbonImmutable::parse($date)->locale('id')->isoFormat('dddd'));
        }
    }

    /** @test */
    public function a_schedule_without_hari_is_skipped_without_error()
    {
        $schedule = $this->schedule(['hari' => null]);

        $this->assertSame([], $this->dates($schedule, '2026-07-01', '2026-07-31'));
    }

    /** @test */
    public function occurrences_are_sorted_by_date_then_time()
    {
        $late = $this->schedule(['hari' => 'Rabu', 'waktu' => '2026-01-01 19:30:00', 'nama_jadwal' => 'Malam']);
        $early = $this->schedule(['hari' => 'Rabu', 'waktu' => '2026-01-01 06:00:00', 'nama_jadwal' => 'Pagi']);
        $otherDay = $this->schedule(['hari' => 'Selasa', 'waktu' => '2026-01-01 20:00:00', 'nama_jadwal' => 'Selasa']);

        $occurrences = $this->service->expand(
            new Collection([$late, $early, $otherDay]),
            CarbonImmutable::parse('2026-07-21', HijriConverter::TIMEZONE),
            CarbonImmutable::parse('2026-07-27', HijriConverter::TIMEZONE),
        );

        $order = array_map(
            fn (array $o) => $o['date']->toDateString().' '.$o['schedule']->nama_jadwal,
            $occurrences
        );

        $this->assertSame(['2026-07-21 Selasa', '2026-07-22 Pagi', '2026-07-22 Malam'], $order);
    }
}
