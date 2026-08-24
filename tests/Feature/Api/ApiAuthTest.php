<?php

namespace Tests\Feature\Api;

class ApiAuthTest extends ApiTestCase
{
    /** @test */
    public function it_rejects_a_request_without_a_key()
    {
        $this->getJson('/api/v1/haul')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'unauthenticated');
    }

    /** @test */
    public function it_rejects_an_unknown_prefix()
    {
        $this->getJson('/api/v1/haul', ['X-API-Key' => 'unknown0keythatdoesnotexistatall12345678'])
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'unauthenticated');
    }

    /** @test */
    public function it_rejects_a_correct_prefix_with_a_wrong_secret()
    {
        $wrong = substr(self::KEY, 0, 8).str_repeat('0', 32);

        $this->getJson('/api/v1/haul', ['X-API-Key' => $wrong])
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'unauthenticated');
    }

    /** @test */
    public function a_revoked_key_is_indistinguishable_from_an_unknown_one()
    {
        $this->client->update(['revoked_at' => now()]);

        $revoked = $this->getJson('/api/v1/haul', ['X-API-Key' => self::KEY]);
        $unknown = $this->getJson('/api/v1/haul', ['X-API-Key' => 'unknown0keythatdoesnotexistatall12345678']);

        $revoked->assertUnauthorized();
        $this->assertSame($unknown->json(), $revoked->json());
    }

    /** @test */
    public function a_valid_key_is_accepted_and_records_last_used_at()
    {
        $this->assertNull($this->client->last_used_at);

        $this->apiGet('/api/v1/haul')->assertOk();

        $this->assertNotNull($this->client->fresh()->last_used_at);
    }

    /** @test */
    public function it_rate_limits_per_client()
    {
        $this->client->update(['rate_limit_per_minute' => 3]);

        for ($i = 0; $i < 3; $i++) {
            $this->apiGet('/api/v1/haul')->assertOk();
        }

        $response = $this->apiGet('/api/v1/haul');

        $response->assertStatus(429)
            ->assertJsonPath('error.code', 'rate_limited')
            ->assertHeader('Retry-After');
    }
}
