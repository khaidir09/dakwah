<?php

namespace App\Http\Controllers\User;

use App\Models\Assembly;
use App\Http\Controllers\Controller;

class MajelisController extends Controller
{
    public function list()
    {
        $assemblies = Assembly::with('teacher')->withCount('schedule')->get();
        return view('pages/user/majelis/list', compact('assemblies'));
    }
}
