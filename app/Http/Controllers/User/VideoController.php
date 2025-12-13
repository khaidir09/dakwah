<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Video;

class VideoController extends Controller
{
    public function list()
    {
        $videos = Video::all();
        return view('pages/user/video/list', compact('videos'));
    }
}
