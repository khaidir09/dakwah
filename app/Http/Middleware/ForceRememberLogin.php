<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membuat setiap sesi login Syaikhuna "diingat" tanpa perlu centang dari pengguna.
 *
 * Fortify menentukan umur sesi dari input request bernama `remember`, yang dibaca di
 * tiga tempat berbeda: AttemptToAuthenticate (login email), RegisteredUserController
 * (registrasi), dan RedirectIfTwoFactorAuthenticatable (yang meneruskannya ke sesi
 * untuk tantangan 2FA). Menyisipkan input itu di sini menutup ketiganya sekaligus.
 *
 * Disisipkan di sisi server, bukan sebagai hidden input di form login, agar tidak bisa
 * dilucuti dari sisi klien. Middleware ini juga ikut jalan di rute Fortify lain (reset
 * password, konfirmasi password) yang tidak pernah membaca `remember` — tidak berdampak.
 *
 * Lihat docs/specs/tetap-login.md
 */
class ForceRememberLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->merge(['remember' => true]);

        return $next($request);
    }
}
