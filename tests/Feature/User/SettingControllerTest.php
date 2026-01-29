<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_update_saves_gender_and_birth_year()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->put(route('pengaturan-akun.update'), [
            'name' => 'Test Name',
            'email' => 'test@example.com',
            'gender' => 'Laki-laki',
            'birth_year' => 1990,
        ]);

        $response->assertSessionHas('status', 'profile-updated');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'gender' => 'Laki-laki',
            'birth_year' => 1990,
        ]);
    }

    public function test_incomplete_profile_warning_is_shown()
    {
        $user = User::factory()->create([
            'gender' => null,
            'birth_year' => null,
            'province_code' => null,
        ]);

        $this->actingAs($user);

        // Simulate the redirect from Google Auth with session
        $response = $this->withSession(['incomplete_profile' => true])
                         ->get(route('pengaturan-akun'));

        $response->assertSee('Silahkan lengkapi data informasi');
        $response->assertSee('Data Personal');
        $response->assertSee('Jenis Kelamin');
        $response->assertSee('Tahun Lahir');
    }
}
