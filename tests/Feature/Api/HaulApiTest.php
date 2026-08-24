<?php

namespace Tests\Feature\Api;

use App\Services\HijriConverter;
use Carbon\CarbonImmutable;

class HaulApiTest extends ApiTestCase
{
    private function hijriToday(): array
    {
        return (new HijriConverter)->toHijri(CarbonImmutable::now(HijriConverter::TIMEZONE));
    }

    /** @test */
    public function it_returns_upcoming_hauls_with_the_expected_structure()
    {
        $this->makeTeacher([
            'name' => 'KH. Uji',
            'wafat_hijriah_day' => 5,
            'wafat_hijriah_month' => 7,
            'wafat_hijriah_year' => 1426,
            'wafat_masehi' => '2005-08-10',
        ]);

        $this->apiGet('/api/v1/haul')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'id', 'nama', 'slug', 'wafat_hijriah_label',
                    'wafat_hijriah_day', 'wafat_hijriah_month', 'wafat_hijriah_year',
                    'wafat_masehi', 'haul_berikutnya_masehi', 'hari_menuju_haul', 'foto_url', 'url',
                ]],
                'meta' => ['hijri_today' => ['day', 'month', 'year']],
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.wafat_hijriah_label', '5 Rajab 1426');
    }

    /** @test */
    public function it_excludes_teachers_who_are_still_alive()
    {
        $this->makeTeacher(['name' => 'Guru Hidup']);

        $this->apiGet('/api/v1/haul')->assertOk()->assertJsonCount(0, 'data');
    }

    /** @test */
    public function it_excludes_teachers_without_a_hijri_day_or_month()
    {
        $this->makeTeacher([
            'name' => 'Tanpa Tanggal',
            'wafat_hijriah_year' => 1440,
            'wafat_hijriah_day' => null,
            'wafat_hijriah_month' => null,
        ]);

        $this->apiGet('/api/v1/haul')->assertOk()->assertJsonCount(0, 'data');
    }

    /** @test */
    public function haul_dates_are_always_in_the_future_and_sorted_ascending()
    {
        $hijri = $this->hijriToday();

        foreach ([1, 5, 9, 12] as $index => $month) {
            $this->makeTeacher([
                'name' => 'Guru Bulan '.$month,
                'wafat_hijriah_day' => 10,
                'wafat_hijriah_month' => $month,
                'wafat_hijriah_year' => 1400 + $index,
            ]);
        }

        $response = $this->apiGet('/api/v1/haul')->assertOk();

        $days = array_column($response->json('data'), 'hari_menuju_haul');

        $this->assertCount(4, $days);
        $this->assertSame($days, array_values(collect($days)->sort()->all()), 'Hasil tidak terurut menaik.');

        foreach ($days as $value) {
            $this->assertGreaterThanOrEqual(0, $value);
        }

        $this->assertSame($hijri, $response->json('meta.hijri_today'));
    }

    /** @test */
    public function it_respects_the_limit_parameter_and_its_bounds()
    {
        for ($i = 1; $i <= 4; $i++) {
            $this->makeTeacher([
                'name' => 'Guru '.$i,
                'wafat_hijriah_day' => $i,
                'wafat_hijriah_month' => $i,
                'wafat_hijriah_year' => 1400,
            ]);
        }

        $this->apiGet('/api/v1/haul?limit=2')->assertOk()->assertJsonCount(2, 'data');
        $this->apiGet('/api/v1/haul?limit=0')->assertStatus(422);
        $this->apiGet('/api/v1/haul?limit=51')->assertStatus(422);
    }

    /** @test */
    public function foto_url_is_absolute_and_null_when_missing()
    {
        $this->makeTeacher([
            'name' => 'Tanpa Foto',
            'foto' => null,
            'wafat_hijriah_day' => 1,
            'wafat_hijriah_month' => 1,
            'wafat_hijriah_year' => 1400,
        ]);
        $this->makeTeacher([
            'name' => 'Dengan Foto',
            'foto' => 'guru/ada.webp',
            'wafat_hijriah_day' => 2,
            'wafat_hijriah_month' => 1,
            'wafat_hijriah_year' => 1400,
        ]);

        $data = collect($this->apiGet('/api/v1/haul')->assertOk()->json('data'))->keyBy('nama');

        $this->assertNull($data['Tanpa Foto']['foto_url']);
        $this->assertStringStartsWith('http', $data['Dengan Foto']['foto_url']);
    }

    /** @test */
    public function it_does_not_expose_the_biography()
    {
        $this->makeTeacher([
            'name' => 'KH. Uji',
            'biografi' => '<p>biografi panjang</p>',
            'wafat_hijriah_day' => 1,
            'wafat_hijriah_month' => 1,
            'wafat_hijriah_year' => 1400,
        ]);

        $response = $this->apiGet('/api/v1/haul')->assertOk();

        $this->assertArrayNotHasKey('biografi', $response->json('data.0'));
    }
}
