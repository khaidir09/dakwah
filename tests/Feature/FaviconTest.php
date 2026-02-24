<?php

namespace Tests\Feature;

use Tests\TestCase;

class FaviconTest extends TestCase
{
    /**
     * Test if favicon links are present on the login page.
     */
    public function test_favicon_links_are_present_on_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('apple-touch-icon.png');
        $response->assertSee('favicon-32x32.png');
        $response->assertSee('favicon-16x16.png');
        $response->assertSee('site.webmanifest');
    }
}
