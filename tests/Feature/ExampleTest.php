<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Sejak beranda pindah dari /beranda ke root, '/' merender halaman penuh
     * (bukan lagi redirect), sehingga test ini butuh database dan HTTP palsu.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->withoutVite();

        Http::fake(['*' => Http::response([], 200)]);

        $response = $this->get('/');

        $response->assertOk();
    }
}
