<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiClient
{
    public const ATTRIBUTE = 'api_client';

    public function handle(Request $request, Closure $next): Response
    {
        $client = self::resolveClient($request);

        // Key yang dicabut dan key yang tidak dikenal sengaja tidak dibedakan,
        // agar response tidak membocorkan bahwa sebuah key pernah valid.
        if (! $client || ! Hash::check(self::key($request), $client->key_hash)) {
            return $this->unauthenticated();
        }

        $request->attributes->set(self::ATTRIBUTE, $client);

        if (! $client->last_used_at || $client->last_used_at->lt(now()->subMinute())) {
            $client->forceFill(['last_used_at' => now()])->saveQuietly();
        }

        return $next($request);
    }

    /**
     * Pencarian berbasis prefix saja (indexed, tanpa verifikasi hash).
     * Dipakai bersama oleh middleware ini dan limiter `api-public`, yang
     * bisa berjalan lebih dulu karena penyusunan ulang prioritas middleware.
     */
    public static function resolveClient(Request $request): ?ApiClient
    {
        $key = self::key($request);

        if (strlen($key) < ApiClient::PREFIX_LENGTH) {
            return null;
        }

        return ApiClient::active()
            ->where('key_prefix', substr($key, 0, ApiClient::PREFIX_LENGTH))
            ->first();
    }

    private static function key(Request $request): string
    {
        return (string) $request->header('X-API-Key', '');
    }

    private function unauthenticated(): Response
    {
        return response()->json([
            'error' => [
                'code' => 'unauthenticated',
                'message' => 'API key tidak valid atau tidak disertakan.',
            ],
        ], Response::HTTP_UNAUTHORIZED);
    }
}
