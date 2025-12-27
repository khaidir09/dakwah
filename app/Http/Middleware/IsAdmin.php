<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Check if user has admin-like roles.
            // Using strict check for 'admin' as primary, but allowing 'Super Admin' if it exists in the system.
            if ($user->hasRole('admin') || $user->hasRole('Super Admin')) {
                return $next($request);
            }
        }

        // If not admin, abort with 403 Forbidden
        abort(403, 'User does not have the right roles.');
    }
}
