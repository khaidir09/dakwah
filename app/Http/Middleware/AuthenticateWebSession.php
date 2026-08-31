<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Session\Middleware\AuthenticatesSessions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mencabut sesi di perangkat lain begitu password pengguna berubah.
 *
 * Dua middleware bawaan sudah ada untuk keperluan ini, tetapi masing-masing punya
 * lubang pada konfigurasi aplikasi ini, jadi keduanya digabung di sini:
 *
 * - `Laravel\Jetstream\Http\Middleware\AuthenticateSession` memeriksa hash password
 *   di cookie recaller dengan benar, tetapi memakai kunci sesi
 *   `password_hash_{Auth::getDefaultDriver()}`. Pada rute ber-`auth:sanctum` driver
 *   default menjadi `sanctum`, sedangkan form ganti password Jetstream berjalan lewat
 *   `livewire/update` yang hanya ber-middleware `web` sehingga menulis
 *   `password_hash_web`. Kunci `password_hash_sanctum` tidak pernah ikut disegarkan
 *   (listener-nya hanya didaftarkan pada stack Inertia, sedangkan proyek ini Livewire),
 *   sehingga pengguna yang mengganti passwordnya sendiri ikut ter-logout.
 *
 * - `Laravel\Sanctum\Http\Middleware\AuthenticateSession` memakai kunci yang benar
 *   (`config('sanctum.guard')` = `web`), tetapi sama sekali tidak memeriksa cookie
 *   recaller — perangkat yang sesinya sudah kedaluwarsa dan hanya berbekal cookie
 *   remember tidak akan pernah tercabut.
 *
 * Middleware ini mengunci guard secara eksplisit ke `web` supaya perilakunya sama
 * pada ketiga grup rute terproteksi, baik yang memakai `auth` maupun `auth:sanctum`.
 *
 * Lihat docs/specs/tetap-login.md
 */
class AuthenticateWebSession implements AuthenticatesSessions
{
    public const GUARD = 'web';

    /**
     * Umur cookie recaller saat diterbitkan ulang, menyamai
     * `SessionGuard::$rememberDuration` (576000 menit ≈ 400 hari) yang tidak punya
     * accessor publik. 400 hari juga plafon umur cookie di Chrome dan Safari.
     */
    public const RECALLER_DURATION = 576000;

    public function __construct(protected AuthFactory $auth) {}

    public function handle(Request $request, Closure $next): Response
    {
        $guard = $this->auth->guard(self::GUARD);

        if (! $request->hasSession() || ! $guard->check() || ! $guard->user()->getAuthPassword()) {
            return $next($request);
        }

        $key = 'password_hash_'.self::GUARD;
        $passwordHash = $guard->user()->getAuthPassword();

        // Perangkat yang hanya berbekal cookie remember: hash password ikut disimpan
        // sebagai ruas ketiga di dalam cookie recaller, jadi cukup dicocokkan dari sana.
        if ($guard->viaRemember()) {
            $hashDiCookie = explode('|', (string) $request->cookies->get($guard->getRecallerName()))[2] ?? null;

            if (! $hashDiCookie || ! hash_equals($passwordHash, $hashDiCookie)) {
                $this->logout($request, $guard);
            }
        }

        // Sesi yang sudah berjalan sebelum middleware ini dipasang belum punya kunci ini;
        // sesi seperti itu diisi, bukan dicabut, supaya deploy tidak menendang semua orang.
        if ($request->session()->has($key) && ! hash_equals($request->session()->get($key), $passwordHash)) {
            $this->logout($request, $guard);
        }

        $request->session()->put($key, $passwordHash);

        return tap($next($request), function () use ($request, $guard, $key) {
            if (! $guard->hasUser()) {
                return;
            }

            $hashTerkini = $guard->user()->getAuthPassword();

            $request->session()->put($key, $hashTerkini);

            $this->refreshRecallerCookie($request, $guard, $hashTerkini);
        });
    }

    /**
     * Menerbitkan ulang cookie recaller bila password berubah di dalam request ini.
     *
     * Tanpa ini, perangkat yang baru saja mengganti passwordnya sendiri tetap memegang
     * cookie berisi hash lama. Sesinya memang masih hidup sehingga tidak terasa apa-apa,
     * tetapi begitu sesi itu kedaluwarsa dan pengguna kembali hanya berbekal cookie,
     * cocokan hash di atas akan menolaknya — pengguna ter-logout tanpa sebab yang jelas,
     * dan justru kehilangan manfaat fitur "tetap login" ini.
     *
     * Mengikuti pola `SessionGuard::logoutOtherDevices()`, yang juga menerbitkan ulang
     * cookie recaller setelah hash password berubah.
     */
    protected function refreshRecallerCookie(Request $request, $guard, string $hashTerkini): void
    {
        $cookieLama = $request->cookies->get($guard->getRecallerName());

        if ($cookieLama === null) {
            return;
        }

        if ((explode('|', (string) $cookieLama)[2] ?? null) === $hashTerkini) {
            return;
        }

        Cookie::queue(
            $guard->getRecallerName(),
            $guard->user()->getAuthIdentifier().'|'.$guard->user()->getRememberToken().'|'.$hashTerkini,
            self::RECALLER_DURATION
        );
    }

    /**
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function logout(Request $request, $guard): never
    {
        $guard->logoutCurrentDevice();

        $request->session()->flush();

        throw new AuthenticationException('Unauthenticated.', [self::GUARD]);
    }
}
