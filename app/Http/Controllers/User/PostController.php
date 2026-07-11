<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        return view('pages.user.post.index');
    }

    public function detail($slug)
    {
        $post = Post::published()->where('slug', $slug)->with(['user', 'labels'])->firstOrFail();

        // Agar guest yang klik "Login untuk Mengunduh" kembali ke halaman ini setelah login.
        if ($post->attachment_path && ! auth()->check()) {
            session()->put('url.intended', route('tulisan.detail', $post->slug));
        }

        return view('pages.user.post.detail', compact('post'));
    }

    public function download($slug)
    {
        abort_if(! auth()->check(), 403, 'Unauthorized. Anda harus login untuk mengunduh file ini.');

        $post = Post::published()->where('slug', $slug)->firstOrFail();

        if (! $post->attachment_path || ! Storage::disk('local')->exists($post->attachment_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $post->increment('downloads_count');

        return Storage::disk('local')->download($post->attachment_path, $post->attachment_filename);
    }
}
