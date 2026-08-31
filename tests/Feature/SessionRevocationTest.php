<?php

namespace Tests\Feature;

use App\Http\Middleware\AuthenticateWebSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Mengganti atau me-reset password mencabut sesi di perangkat lain, lewat middleware
 * AuthenticateSession yang dipasang di ketiga grup rute terproteksi.
 *
 * Diuji dari dua sisi: yang HARUS ter-logout, dan yang TIDAK BOLEH ter-logout.
 * Sisi kedua yang paling penting dijaga — regresi di situ me-logout semua pengguna
 * sekaligus, bukan hanya menggagalkan satu fitur.
 *
 * Lihat docs/specs/tetap-login.md
 */
class SessionRevocationTest extends TestCase
{
    use RefreshDatabase;

    private function recallerName(): string
    {
        return Auth::guard('web')->getRecallerName();
    }

    private function recallerFor(User $user, ?string $passwordHash = null): string
    {
        return $user->id.'|'.$user->getRememberToken().'|'.($passwordHash ?? $user->password);
    }

    /**
     * Guard dikunci ke `web`, bukan Auth::getDefaultDriver(). Pada rute ber-`auth:sanctum`
     * driver default berubah menjadi `sanctum`, sedangkan kunci yang benar-benar dipakai
     * dan disegarkan di seluruh aplikasi adalah milik guard `web`.
     */
    private function sessionKey(): string
    {
        return 'password_hash_'.AuthenticateWebSession::GUARD;
    }

    public function test_ganti_password_mencabut_sesi_perangkat_lain(): void
    {
        $user = User::factory()->create();

        // Perangkat lain: sesinya masih menyimpan hash password sebelum penggantian.
        $this->actingAs($user)
            ->withSession([$this->sessionKey() => 'hash-password-lama'])
            ->get(route('pustaka-saya'))
            ->assertRedirect(route('login'));

        $this->assertGuest(AuthenticateWebSession::GUARD);
    }

    /**
     * Jalur pencabutan untuk perangkat yang sesinya sudah kedaluwarsa dan hanya
     * berbekal cookie remember. Hash password ikut tersimpan di dalam cookie itu,
     * jadi penggantian password mematikannya tanpa perlu menyentuh remember_token.
     */
    public function test_cookie_remember_dengan_hash_password_lama_ditolak(): void
    {
        $user = User::factory()->create(['remember_token' => Str::random(60)]);
        $recallerLama = $this->recallerFor($user);

        $user->forceFill(['password' => Hash::make('password-baru')])->save();

        $this->withCookie($this->recallerName(), $recallerLama)
            ->get(route('pustaka-saya'))
            ->assertRedirect(route('login'));

        $this->assertGuest(AuthenticateWebSession::GUARD);
    }

    /**
     * Reset password lewat "Lupa Password?" memakai mekanisme yang sama: broker
     * hanya mengubah kolom password dan tidak menyentuh remember_token, sehingga
     * recaller di perangkat lain otomatis basi.
     */
    public function test_reset_password_mencabut_sesi_perangkat_lain(): void
    {
        $user = User::factory()->create(['remember_token' => Str::random(60)]);
        $recallerLama = $this->recallerFor($user);

        $this->post('/forgot-password', ['email' => $user->email]);
        $token = app('auth.password.broker')->createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ]);

        $this->withCookie($this->recallerName(), $recallerLama)
            ->get(route('pustaka-saya'))
            ->assertRedirect(route('login'));

        $this->assertGuest(AuthenticateWebSession::GUARD);
    }

    public function test_perangkat_yang_mengganti_password_tetap_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession([$this->sessionKey() => $user->password])
            ->get(route('pustaka-saya'))
            ->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Sesi yang sudah berjalan sebelum middleware ini dipasang belum menyimpan
     * password_hash_web sama sekali. Middleware harus mengisinya, bukan me-logout —
     * kalau tidak, deploy fitur ini akan menendang seluruh pengguna yang sedang login.
     */
    public function test_sesi_tanpa_hash_password_tidak_dicabut(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('pustaka-saya'))
            ->assertOk();

        $this->assertAuthenticatedAs($user);
        $this->assertSame($user->password, session($this->sessionKey()));
    }

    /**
     * Regresi paling mahal di area ini: pengguna mengganti passwordnya sendiri lalu
     * ikut tertendang dari perangkatnya sendiri.
     *
     * Lewat rute HTTP `PUT /user/password`, yang memicu PasswordUpdatedViaController.
     * Jetstream hanya mendaftarkan listener penyegar sesi untuk event itu pada stack
     * Inertia, jadi proyek ini mendaftarkannya sendiri di EventServiceProvider.
     */
    public function test_ganti_password_tidak_melogout_perangkat_sendiri(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('pustaka-saya'))->assertOk();

        $this->put('/user/password', [
            'current_password' => 'password',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ])->assertSessionHasNoErrors();

        $this->get(route('pustaka-saya'))->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Mengunci akar penyebab bug tersebut: pada rute ber-`auth:sanctum`, guard default
     * berubah menjadi `sanctum`. Middleware harus tetap memakai kunci guard `web` —
     * kunci yang sama yang disegarkan form ganti password lewat `livewire/update`.
     * Kalau ini bergeser ke `password_hash_sanctum`, kunci itu tidak akan pernah
     * ikut disegarkan dan pengguna ter-logout dari perangkatnya sendiri.
     */
    public function test_kunci_sesi_tetap_milik_guard_web_di_rute_sanctum(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('pustaka-saya'))->assertOk();

        $this->assertSame($user->password, session('password_hash_web'));
        $this->assertNull(session('password_hash_sanctum'));
    }

    /**
     * Ganti password lewat /pengaturan-akun (SettingController@update) — jalur ketiga,
     * di samping form Jetstream dan PUT /user/password.
     *
     * Cookie recaller perangkat ini harus ikut diterbitkan ulang dengan hash baru.
     * Kalau tidak, sesinya memang masih hidup sehingga tidak terasa apa-apa sekarang,
     * tetapi begitu sesi kedaluwarsa (7 hari) pengguna kembali hanya berbekal cookie
     * berisi hash lama lalu ditolak — kehilangan manfaat fitur "tetap login" ini.
     */
    public function test_cookie_remember_diterbitkan_ulang_saat_ganti_password_sendiri(): void
    {
        $user = User::factory()->create(['remember_token' => Str::random(60)]);

        $response = $this->actingAs($user)
            ->withCookie($this->recallerName(), $this->recallerFor($user))
            ->put(route('pengaturan-akun.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'current_password' => 'password',
                'password' => 'password-baru-123',
            ]);

        $response->assertSessionHasNoErrors();

        $segar = $user->fresh();

        $response->assertCookie(
            $this->recallerName(),
            $segar->id.'|'.$segar->getRememberToken().'|'.$segar->password
        );
    }

    /**
     * Cookie yang baru diterbitkan itu harus benar-benar diterima pada kunjungan
     * berikutnya, termasuk ketika sesinya sudah habis.
     */
    public function test_cookie_remember_baru_masih_berlaku_setelah_sesi_habis(): void
    {
        $user = User::factory()->create(['remember_token' => Str::random(60)]);

        $this->actingAs($user)
            ->withCookie($this->recallerName(), $this->recallerFor($user))
            ->put(route('pengaturan-akun.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'current_password' => 'password',
                'password' => 'password-baru-123',
            ])->assertSessionHasNoErrors();

        $this->flushSession();
        Auth::forgetGuards();

        $this->withCookie($this->recallerName(), $this->recallerFor($user->fresh()))
            ->get(route('pengaturan-akun'))
            ->assertOk();

        $this->assertAuthenticatedAs($user->fresh(), AuthenticateWebSession::GUARD);
    }

    public function test_pencabutan_berlaku_di_grup_pengaturan_akun(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession([$this->sessionKey() => 'hash-password-lama'])
            ->get(route('pengaturan-akun'))
            ->assertRedirect(route('login'));

        $this->assertGuest(AuthenticateWebSession::GUARD);
    }

    public function test_pencabutan_berlaku_di_area_admin(): void
    {
        $admin = User::factory()->create();
        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin->assignRole('Super Admin');

        $this->actingAs($admin)
            ->withSession([$this->sessionKey() => 'hash-password-lama'])
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest(AuthenticateWebSession::GUARD);
    }
}
