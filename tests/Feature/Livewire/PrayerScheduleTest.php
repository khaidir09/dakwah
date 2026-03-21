<?php

namespace Tests\Feature\Livewire;

use App\Livewire\PrayerSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;
use Livewire\Livewire;
use Tests\TestCase;

class PrayerScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:2fl+Ktvkfl+Fuz4Qh/Lx1uwMfwSbcKoAprn67x79FOY=']);
    }

    /** @test */
    public function it_uses_default_city_for_guest()
    {
        $defaultCityId = '2f2b265625d76a6704b08093c652fd79';

        Http::fake([
            "api.myquran.com/v3/sholat/jadwal/{$defaultCityId}/*" => Http::response([
                'status' => true,
                'data' => [
                    'lokasi' => 'Hulu Sungai Utara',
                    'jadwal' => [
                        '2024-01-01' => [
                            'tanggal' => 'Senin, 01/01/2024',
                            'imsak' => '04:30',
                            'subuh' => '04:40',
                            'terbit' => '06:00',
                            'dhuha' => '06:30',
                            'dzuhur' => '12:00',
                            'ashar' => '15:30',
                            'maghrib' => '18:00',
                            'isya' => '19:30',
                        ]
                    ]
                ]
            ], 200),
        ]);

        Livewire::test(PrayerSchedule::class)
            ->assertSet('location', 'Hulu Sungai Utara');
    }

    /** @test */
    public function it_uses_user_city_when_logged_in_and_mapped()
    {
        $province = Province::create(['code' => '63', 'name' => 'Kalimantan Selatan']);
        $city = City::create(['code' => '6372', 'province_code' => '63', 'name' => 'Banjarbaru', 'api_myquran' => 'mapped_id_123']);

        $user = User::factory()->create([
            'city_code' => '6372'
        ]);

        $this->actingAs($user);

        $mappedCityId = 'mapped_id_123';

        Http::fake([
            // Mock Search
            "api.myquran.com/v3/sholat/kota/cari/Banjarbaru" => Http::response([
                'status' => true,
                'data' => [
                    [
                        'id' => $mappedCityId,
                        'lokasi' => 'KOTA BANJARBARU'
                    ]
                ]
            ], 200),

            // Mock Schedule with Mapped ID
            "api.myquran.com/v3/sholat/jadwal/{$mappedCityId}/*" => Http::response([
                'status' => true,
                'data' => [
                    'lokasi' => 'KOTA BANJARBARU',
                    'jadwal' => [
                         '2024-01-01' => [
                            'tanggal' => 'Senin, 01/01/2024',
                            'imsak' => '04:35',
                            'subuh' => '04:45',
                            'terbit' => '06:05',
                            'dhuha' => '06:35',
                            'dzuhur' => '12:05',
                            'ashar' => '15:35',
                            'maghrib' => '18:05',
                            'isya' => '19:35',
                        ]
                    ]
                ]
            ], 200),

             // Fallback default just in case logic fails (so test doesn't crash on unmocked request if it falls back)
            "api.myquran.com/v3/sholat/jadwal/2f2b265625d76a6704b08093c652fd79/*" => Http::response([
                'status' => true,
                'data' => [
                    'lokasi' => 'Hulu Sungai Utara',
                    'jadwal' => [
                        '2024-01-01' => [
                            'tanggal' => 'Senin, 01/01/2024',
                            'imsak' => '04:30',
                            'subuh' => '04:40',
                            'terbit' => '06:00',
                            'dhuha' => '06:30',
                            'dzuhur' => '12:00',
                            'ashar' => '15:30',
                            'maghrib' => '18:00',
                            'isya' => '19:30',
                        ]
                    ]
                ]
            ], 200),
        ]);

        Livewire::test(PrayerSchedule::class)
            ->assertSet('location', 'KOTA BANJARBARU');
    }
}
