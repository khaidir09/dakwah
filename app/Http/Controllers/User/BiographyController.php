<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

class BiographyController extends Controller
{
    public function list()
    {
        return view('pages.user.biography.list');
    }

    public function detail($slug)
    {
        $biography = Teacher::with('contributor')->where('slug', $slug)->firstOrFail();

        abort_unless($biography->isVisibleTo(Auth::user()), 404);

        return view('pages.user.biography.detail', compact('biography'));
    }
}
