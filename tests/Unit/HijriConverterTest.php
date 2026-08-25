<?php

namespace Tests\Unit;

use App\Services\HijriConverter;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class HijriConverterTest extends TestCase
{
    private HijriConverter $converter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = new HijriConverter;
    }

    /** @test */
    public function it_converts_hijri_to_gregorian()
    {
        $this->assertSame(
            '2026-01-20',
            $this->converter->toGregorian(1, 8, 1447)->toDateString()
        );
    }

    /** @test */
    public function it_converts_gregorian_to_hijri()
    {
        $hijri = $this->converter->toHijri(
            CarbonImmutable::parse('2026-01-20', HijriConverter::TIMEZONE)
        );

        $this->assertSame(['day' => 1, 'month' => 8, 'year' => 1447], $hijri);
    }

    /** @test */
    public function it_round_trips_consistently()
    {
        $date = CarbonImmutable::parse('2026-07-21', HijriConverter::TIMEZONE);
        $hijri = $this->converter->toHijri($date);

        $this->assertSame(
            $date->toDateString(),
            $this->converter->toGregorian($hijri['day'], $hijri['month'], $hijri['year'])->toDateString()
        );
    }

    /** @test */
    public function it_clamps_a_day_beyond_the_hijri_month_length()
    {
        // Zulhijjah 1446 berumur 29 hari (bukan tahun kabisat Hijriah).
        $clamped = $this->converter->toGregorian(30, 12, 1446);
        $lastDay = $this->converter->toGregorian(29, 12, 1446);

        $this->assertSame($lastDay->toDateString(), $clamped->toDateString());

        // Tidak boleh tumpah ke bulan berikutnya.
        $this->assertSame(12, $this->converter->toHijri($clamped)['month']);
        $this->assertSame(29, $this->converter->toHijri($clamped)['day']);
    }
}
