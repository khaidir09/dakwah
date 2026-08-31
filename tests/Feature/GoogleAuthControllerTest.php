<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

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

    /**
     * Menyiapkan mock Socialite untuk satu akun Google.
     */
    private function mockGoogleUser(string $email, string $name = 'Test User', int $id = 1234567890): void
    {
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')
            ->andReturn($id)
            ->shouldReceive('getEmail')
            ->andReturn($email)
            ->shouldReceive('getName')
            ->andReturn($name);

        $abstractUser->id = $id;
        $abstractUser->email = $email;
        $abstractUser->name = $name;

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_login_google_menerbitkan_cookie_remember(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'gender' => 'Laki-laki',
            'birth_year' => 1990,
            'province_code' => '12',
            'remember_token' => null,
        ]);

        $this->mockGoogleUser('test@example.com');

        $response = $this->get('auth/google');

        $this->assertAuthenticatedAs($user);
        $response->assertCookie(Auth::guard('web')->getRecallerName());
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_login_google_menerbitkan_cookie_remember_untuk_pengguna_baru(): void
    {
        $this->mockGoogleUser('baru@example.com', 'Pengguna Baru');

        $response = $this->get('auth/google');

        $this->assertAuthenticated();
        $response->assertCookie(Auth::guard('web')->getRecallerName());
        $this->assertNotNull(User::where('email', 'baru@example.com')->sole()->remember_token);
    }

    /**
     * Jalur Google tidak melewati PrepareAuthenticatedSession milik Fortify, jadi
     * regenerasi ID sesi harus dilakukan controller sendiri — kalau tidak, ID sesi
     * pra-login terbawa ke sesi terautentikasi yang kini berumur panjang.
     */
    public function test_login_google_meregenerasi_id_sesi(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'gender' => 'Laki-laki',
            'birth_year' => 1990,
            'province_code' => '12',
        ]);

        $this->mockGoogleUser('test@example.com');

        $this->startSession();
        $idSebelum = session()->getId();

        $this->get('auth/google');

        $this->assertNotSame($idSebelum, session()->getId());
    }
}
