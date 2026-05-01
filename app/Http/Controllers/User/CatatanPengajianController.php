<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class CatatanPengajianController extends Controller
{
    public function index()
    {
        return view('pages.user.catatan-pengajian.list');
    }
}
