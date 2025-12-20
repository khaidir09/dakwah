<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WiridController extends Controller
{
    public function list()
    {
        return view('pages.user.wirid.list');
    }
}
