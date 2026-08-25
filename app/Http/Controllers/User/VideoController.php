<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class VideoController extends Controller
{
    public function list()
    {
        // Daftar video dirender oleh <livewire:list-video />, yang memuat
        // datanya sendiri dengan paginasi.
        return view('pages/user/video/list');
    }
}
