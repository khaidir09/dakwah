<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Sesi login Syaikhuna bertahan lewat cookie "remember me" yang selalu aktif.
 *
 * Lihat docs/specs/tetap-login.md
 */
class PersistentLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // CreateNewUser memanggil assignRole(2), jadi role harus ada sebelum registrasi diuji.
        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::create(['name' => 'User', 'guard_name' => 'web']);
    }

    private function recallerName(): string
    {
        return Auth::guard('web')->getRecallerName();
    }

    /**
     * Merakit isi cookie recaller persis seperti SessionGuard::queueRecallerCookie().
     *
     * Dikirim lewat withCookie() (bukan withUnencryptedCookie), karena EncryptCookies
     * aktif tanpa pengecualian di aplikasi ini: harness test yang mengenkripsinya.
     */
    private function recallerFor(User $user, ?string $passwordHash = null): string
    {
        return $user->id.'|'.$user->getRememberToken().'|'.($passwordHash ?? $user->password);
    }

    public function test_login_email_menerbitkan_cookie_remember(): void
    {
        $user = User::factory()->create(['remember_token' => null]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertCookie($this->recallerName());
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_registrasi_menerbitkan_cookie_remember(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jamaah Baru',
            'email' => 'jamaah.baru@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'province_code' => '63',
            'city_code' => '6371',
            'district_code' => '637101',
            'village_code' => '6371011001',
            'gender' => 'Laki-laki',
            'birth_year' => 1990,
        ]);

        $this->assertAuthenticated();
        $response->assertCookie($this->recallerName());
        $this->assertNotNull(User::where('email', 'jamaah.baru@example.com')->sole()->remember_token);
    }

    public function test_super_admin_juga_mendapat_cookie_remember(): void
    {
        $admin = User::factory()->create(['remember_token' => null]);
        $admin->assignRole('Super Admin');

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertCookie($this->recallerName());
        $this->assertNotNull($admin->fresh()->remember_token);
    }

    /**
     * Test inti fitur: tanpa sesi sama sekali, hanya berbekal cookie recaller,
     * pengguna tetap dikenali. Inilah yang membuat "tidak perlu login berulang"
     * bertahan melewati kedaluwarsanya baris di tabel `sessions`.
     */
    public function test_sesi_kedaluwarsa_dipulihkan_dari_cookie_remember(): void
    {
        $user = User::factory()->create(['remember_token' => Str::random(60)]);

        $this->withCookie($this->recallerName(), $this->recallerFor($user))
            ->get(route('pengaturan-akun'))
            ->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_logout_menghapus_cookie_remember(): void
    {
        $user = User::factory()->create(['remember_token' => Str::random(60)]);

        // Cookie recaller ikut dikirim karena browser sungguhan selalu mengembalikannya;
        // tanpa itu SessionGuard tidak punya alasan menerbitkan cookie penghapus.
        $response = $this->actingAs($user)
            ->withCookie($this->recallerName(), $this->recallerFor($user))
            ->post('/logout');

        $this->assertGuest();
        $response->assertCookieExpired($this->recallerName());
    }

    /**
     * Yang memberi arti keamanan pada test di atas: SessionGuard::logout() mengganti
     * remember_token, sehingga salinan cookie lama yang sempat diambil orang lain
     * pun ikut mati — bukan sekadar cookie di browser ini yang dihapus.
     */
    public function test_cookie_remember_lama_tidak_berlaku_setelah_logout(): void
    {
        $user = User::factory()->create(['remember_token' => Str::random(60)]);
        $recallerLama = $this->recallerFor($user);
        // Disalin lebih dulu: cycleRememberToken() memutasi objek $user ini juga.
        $tokenLama = $user->remember_token;

        $this->actingAs($user)
            ->withCookie($this->recallerName(), $recallerLama)
            ->post('/logout');

        $this->assertNotSame($tokenLama, $user->fresh()->remember_token);

        $this->withCookie($this->recallerName(), $recallerLama)
            ->get(route('pengaturan-akun'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_pengguna_belum_verifikasi_email_tetap_dicegat(): void
    {
        $user = User::factory()->unverified()->create(['remember_token' => Str::random(60)]);

        $this->withCookie($this->recallerName(), $this->recallerFor($user))
            ->get(route('pustaka-saya'))
            ->assertRedirect(route('verification.notice'));
    }
}
