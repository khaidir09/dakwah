<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Library;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function list()
    {
        return view('pages.user.library.index');
    }

    public function detail(Library $library)
    {
        return view('pages.user.library.detail', compact('library'));
    }
}
