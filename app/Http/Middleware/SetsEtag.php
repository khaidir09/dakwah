<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetsEtag
{
    public function handle(Request $request, Closure $next, int $maxAge = 900): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return $response;
        }

        $etag = '"'.md5((string) $response->getContent()).'"';
        $response->headers->set('ETag', $etag);
        $response->headers->set('Cache-Control', 'public, max-age='.$maxAge);

        $ifNoneMatch = array_map('trim', explode(',', (string) $request->header('If-None-Match', '')));

        if (in_array($etag, $ifNoneMatch, true)) {
            $response->setNotModified();
        }

        return $response;
    }
}
