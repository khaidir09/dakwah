<?php

namespace Tests\Feature\Api;

class JadwalMajelisApiTest extends ApiTestCase
{
    /** @test */
    public function it_returns_occurrences_with_the_expected_structure()
    {
        $teacher = $this->makeTeacher();
        $assembly = $this->makeAssembly();
        $this->makeSchedule($assembly, ['teacher_id' => $teacher->id]);

        $response = $this->apiGet('/api/v1/jadwal-majelis?city_code='.self::CITY);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'schedule_id', 'tanggal', 'hari', 'waktu', 'nama_jadwal', 'recurrence_label', 'access',
                    'majelis' => ['id', 'nama', 'tipe', 'alamat', 'maps', 'city_code', 'district_code', 'gambar_url'],
                    'guru' => ['id', 'nama', 'slug', 'foto_url'],
                    'url',
                ]],
                'meta' => ['city_code', 'days', 'from', 'to', 'is_ramadhan'],
            ])
            ->assertJsonPath('meta.days', 7)
            ->assertJsonCount(1, 'data');

        $this->assertSame('19:30', $response->json('data.0.waktu'));
    }

    /** @test */
    public function it_requires_city_code()
    {
        $this->apiGet('/api/v1/jadwal-majelis')
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['details' => ['city_code']]]);
    }

    /** @test */
    public function it_rejects_an_unknown_city_code()
    {
        $this->apiGet('/api/v1/jadwal-majelis?city_code=9999')
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    /** @test */
    public function a_valid_city_without_schedules_returns_an_empty_list()
    {
        $this->apiGet('/api/v1/jadwal-majelis?city_code='.self::CITY)
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    /** @test */
    public function it_rejects_a_days_value_outside_the_allowed_range()
    {
        $this->apiGet('/api/v1/jadwal-majelis?city_code='.self::CITY.'&days=31')->assertStatus(422);
        $this->apiGet('/api/v1/jadwal-majelis?city_code='.self::CITY.'&days=0')->assertStatus(422);
    }

    /** @test */
    public function it_filters_by_district_and_tipe()
    {
        $assembly = $this->makeAssembly(['tipe' => 'Mesjid']);
        $this->makeSchedule($assembly);

        $this->apiGet('/api/v1/jadwal-majelis?city_code='.self::CITY.'&tipe=Mesjid')
            ->assertOk()->assertJsonCount(1, 'data');

        $this->apiGet('/api/v1/jadwal-majelis?city_code='.self::CITY.'&tipe=Langgar')
            ->assertOk()->assertJsonCount(0, 'data');

        $this->apiGet('/api/v1/jadwal-majelis?city_code='.self::CITY.'&district_code='.self::DISTRICT)
            ->assertOk()->assertJsonCount(1, 'data');
    }

    /** @test */
    public function image_urls_are_absolute_and_the_description_is_not_exposed()
    {
        $assembly = $this->makeAssembly(['gambar' => 'majelis/large/x.webp']);
        $this->makeSchedule($assembly, ['deskripsi' => '<p>rahasia</p>']);

        $response = $this->apiGet('/api/v1/jadwal-majelis?city_code='.self::CITY)->assertOk();

        $this->assertStringStartsWith('http', $response->json('data.0.majelis.gambar_url'));
        $this->assertArrayNotHasKey('deskripsi', $response->json('data.0'));
    }

    /** @test */
    public function it_returns_a_304_when_the_etag_matches()
    {
        $this->makeSchedule($this->makeAssembly());

        $etag = $this->apiGet('/api/v1/jadwal-majelis?city_code='.self::CITY)
            ->assertOk()
            ->headers->get('ETag');

        $this->assertNotNull($etag);

        $this->apiGet('/api/v1/jadwal-majelis?city_code='.self::CITY, ['If-None-Match' => $etag])
            ->assertStatus(304);
    }
}
