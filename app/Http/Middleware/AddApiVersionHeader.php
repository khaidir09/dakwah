<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddApiVersionHeader
{
    public function handle(Request $request, Closure $next, string $version = '1'): Response
    {
        $response = $next($request);

        $response->headers->set('X-Api-Version', $version);

        return $response;
    }
}
