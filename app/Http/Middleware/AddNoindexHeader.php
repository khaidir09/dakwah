<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menandai seluruh response pada route privat agar tidak diindeks.
 *
 * Dikirim sebagai header, bukan hanya <meta name="robots">, karena area privat
 * juga menyajikan PDF, gambar bukti, dan berkas unduhan — response non-HTML
 * yang tidak punya tempat untuk meta tag sama sekali.
 */
class AddNoindexHeader
{
    public const DIRECTIVES = 'noindex, nofollow';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Robots-Tag', self::DIRECTIVES);

        return $response;
    }
}
