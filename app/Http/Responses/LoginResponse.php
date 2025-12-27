<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // Periksa role user yang sedang login
        if (auth()->user()->hasRole('Super Admin')) {
            return redirect()->intended(config('fortify.home'));
        }

        // Default redirect untuk user biasa atau role lain
        return redirect()->intended(route('beranda'));
    }
}
