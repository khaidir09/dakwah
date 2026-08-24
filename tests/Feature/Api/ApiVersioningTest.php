<?php

namespace Tests\Feature\Api;

class ApiVersioningTest extends ApiTestCase
{
    /** @test */
    public function an_unversioned_path_is_not_aliased_to_v1()
    {
        $this->apiGet('/api/jadwal-majelis?city_code='.self::CITY)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'not_found');
    }

    /** @test */
    public function an_unknown_version_returns_a_json_404()
    {
        $this->apiGet('/api/v2/haul')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'not_found');
    }

    /** @test */
    public function successful_responses_carry_the_version_header()
    {
        $this->apiGet('/api/v1/haul')
            ->assertOk()
            ->assertHeader('X-Api-Version', '1');
    }

    /** @test */
    public function error_responses_within_the_version_group_carry_the_version_header()
    {
        $this->getJson('/api/v1/haul')
            ->assertUnauthorized()
            ->assertHeader('X-Api-Version', '1');

        $this->apiGet('/api/v1/jadwal-majelis')
            ->assertStatus(422)
            ->assertHeader('X-Api-Version', '1');
    }
}
