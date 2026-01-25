<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnalyticsTagTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the analytics tag is rendered when config is set.
     */
    public function test_analytics_tag_is_rendered_when_config_is_set(): void
    {
        // Mock configuration
        config(['services.google_analytics.id' => 'G-TEST12345']);

        $response = $this->get('/beranda');

        $response->assertStatus(200);
        $response->assertSee('G-TEST12345');
        $response->assertSee('googletagmanager.com/gtag/js');
    }

    /**
     * Test that the analytics tag is NOT rendered when config is missing.
     */
    public function test_analytics_tag_is_not_rendered_when_config_is_missing(): void
    {
        // Mock configuration
        config(['services.google_analytics.id' => null]);

        $response = $this->get('/beranda');

        $response->assertStatus(200);
        $response->assertDontSee('googletagmanager.com/gtag/js');
    }
}
