<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginPromotionalTextTest extends TestCase
{
    /**
     * Test that the login page contains the promotional text.
     */
    public function test_login_page_contains_promotional_text(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Gunakan akun Google untuk masuk dengan cepat dan aman, Login Praktis & Aman. Kami menggunakan Google OAuth untuk keamanan maksimal. Tidak perlu mengingat password!');
    }
}
