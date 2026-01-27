<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Biography;
use Illuminate\Http\Request;

class BiographyController extends Controller
{
    public function list()
    {
        return view('pages.user.biography.list');
    }

    public function detail($slug)
    {
        $biography = Biography::where('slug', $slug)->firstOrFail();
        return view('pages.user.biography.detail', compact('biography'));
    }
}
