<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Assembly;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirects_to_settings_if_phone_is_missing()
    {
        $user = User::factory()->create([
            'phone' => null,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\User\Onboarding::class)
            ->assertRedirect(route('pengaturan-akun'))
            ->assertSessionHas('error', 'Silakan lengkapi nomor handphone Anda pada pengaturan akun sebelum mendaftarkan majelis.');
    }

    public function test_redirects_if_email_not_verified()
    {
        $user = User::factory()->create([
            'phone' => '08123456789',
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\User\Onboarding::class)
            ->assertRedirect(route('verification.notice'));
    }

    public function test_allows_access_if_phone_is_present()
    {
        $user = User::factory()->create([
            'phone' => '08123456789',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        // Since the component checks for existing assembly, let's ensure user doesn't have one
        Livewire::test(\App\Livewire\User\Onboarding::class)
            ->assertStatus(200);
    }

}
