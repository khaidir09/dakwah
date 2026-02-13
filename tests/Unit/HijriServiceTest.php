<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\HijriService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class HijriServiceTest extends TestCase
{
    public function test_get_hijri_date_string()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn('Rabu, 17 Syakban 1447 H');

        $service = new HijriService();
        $date = $service->getHijriDateString();

        $this->assertEquals('Rabu, 17 Syakban 1447 H', $date);
    }

    public function test_is_ramadhan_returns_true()
    {
        Cache::shouldReceive('remember')
            ->andReturn('1 Ramadhan 1447 H');

        $service = new HijriService();
        $this->assertTrue($service->isRamadhan());
    }

    public function test_is_ramadhan_returns_false()
    {
        Cache::shouldReceive('remember')
            ->andReturn('1 Syawal 1447 H');

        $service = new HijriService();
        $this->assertFalse($service->isRamadhan());
    }
}
