<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Mockery;

class GoogleAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create role with ID 2
        // Note: SQLite might not guarantee ID 2 unless we create 1 then 2 or force ID.
        // Assuming default auto-increment, creating 2 roles is safer.
        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']); // ID 1
        Role::create(['name' => 'User', 'guard_name' => 'web']); // ID 2
    }

    public function test_existing_user_redirects_to_home_if_profile_complete()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'gender' => 'Laki-laki',
            'birth_year' => 1990,
            'province_code' => '12',
        ]);

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')
            ->andReturn(1234567890)
            ->shouldReceive('getEmail')
            ->andReturn('test@example.com')
            ->shouldReceive('getName')
            ->andReturn('Test User');

        // Mock properties directly accessed if any (though methods are usually used)
        $abstractUser->id = 1234567890;
        $abstractUser->email = 'test@example.com';
        $abstractUser->name = 'Test User';

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('auth/google');
        $response->assertRedirect('beranda');
    }

    public function test_existing_user_redirects_to_settings_if_profile_incomplete()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'gender' => null, // Incomplete
            'birth_year' => 1990,
            'province_code' => '12',
        ]);

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')
            ->andReturn(1234567890)
            ->shouldReceive('getEmail')
            ->andReturn('test@example.com')
            ->shouldReceive('getName')
            ->andReturn('Test User');

        $abstractUser->id = 1234567890;
        $abstractUser->email = 'test@example.com';
        $abstractUser->name = 'Test User';

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('auth/google');
        $response->assertRedirect(route('pengaturan-akun'));
        $response->assertSessionHas('incomplete_profile', true);
    }

    public function test_new_user_redirects_to_settings_if_profile_incomplete()
    {
        // No existing user

        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')
            ->andReturn(1234567890)
            ->shouldReceive('getEmail')
            ->andReturn('new@example.com')
            ->shouldReceive('getName')
            ->andReturn('New User');

        $abstractUser->id = 1234567890;
        $abstractUser->email = 'new@example.com';
        $abstractUser->name = 'New User';

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('auth/google');
        $response->assertRedirect(route('pengaturan-akun'));
        $response->assertSessionHas('incomplete_profile', true);

        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }
}
