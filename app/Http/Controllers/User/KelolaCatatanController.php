<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class KelolaCatatanController extends Controller
{
    public function index()
    {
        return view('pages.user.kelola-catatan.index');
    }
}
