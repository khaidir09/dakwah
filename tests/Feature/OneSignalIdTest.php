<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OneSignalIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_onesignal_id()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('user.onesignal.update'), [
            'one_signal_id' => 'test-id-123',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('test-id-123', $user->fresh()->one_signal_id);
    }

    public function test_guest_cannot_update_onesignal_id()
    {
        $response = $this->postJson(route('user.onesignal.update'), [
            'one_signal_id' => 'test-id-123',
        ]);

        $response->assertUnauthorized();
    }
}
