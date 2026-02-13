<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        return view('pages.user.post.index');
    }

    public function detail($slug)
    {
        $post = Post::published()->where('slug', $slug)->with(['user', 'labels'])->firstOrFail();

        return view('pages.user.post.detail', compact('post'));
    }
}
