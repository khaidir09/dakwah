<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use App\Traits\HasImageUploads;

class PostController extends Controller
{
    use HasImageUploads;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.post.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->hasAnyRole(['Super Admin', 'Penulis'])) {
            abort(403, 'Anda tidak memiliki akses untuk membuat tulisan.');
        }
        return view('pages.post.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['Super Admin', 'Penulis'])) {
            abort(403, 'Anda tidak memiliki akses untuk membuat tulisan.');
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => 'required|in:draft,published',
            'labels' => 'nullable|string', // Comma separated tags
            'source' => 'nullable|array',
            'source.*.name' => 'required_with:source|string',
            'source.*.url' => 'nullable|url',
        ]);

        $slug = Str::slug($validatedData['title']);
        // Ensure slug uniqueness
        $count = Post::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . time();
        }

        $post = new Post();
        $post->user_id = Auth::id();
        $post->title = $validatedData['title'];
        $post->slug = $slug;
        $post->content = $validatedData['content'];
        $post->status = $validatedData['status'];
        $post->source = $validatedData['source'] ?? null;

        if ($validatedData['status'] === 'published') {
            $post->published_at = now();
        }

        if ($request->hasFile('cover_image')) {
            // Resize logic similar to Majelis
            $post->cover_image = $this->uploadImage(
                $request->file('cover_image'),
                'posts',
                fn($image) => $image->scaleDown(width: 800)
            );
        }

        $post->save();

        // Handle Labels
        if (!empty($validatedData['labels'])) {
            $labelNames = array_map('trim', explode(',', $validatedData['labels']));
            $labelIds = [];
            foreach ($labelNames as $name) {
                if (empty($name)) continue;
                $slug = Str::slug($name);
                $label = Label::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name]
                );
                $labelIds[] = $label->id;
            }
            $post->labels()->sync($labelIds);
        }

        // Redirect based on role
        if (Auth::user()->hasRole('Super Admin')) {
            return redirect()->route('posts.index')->with('success', 'Tulisan berhasil dibuat.');
        } else {
            return redirect()->route('kelola-tulisan.index')->with('success', 'Tulisan berhasil dibuat.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $post = Post::with('labels')->findOrFail($id);

        // Authorization check
        if (!Auth::user()->hasAnyRole(['Super Admin', 'Penulis'])) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        if (!Auth::user()->hasRole('Super Admin') && $post->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('pages.post.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // Authorization check
        if (!Auth::user()->hasAnyRole(['Super Admin', 'Penulis'])) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        if (!Auth::user()->hasRole('Super Admin') && $post->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => 'required|in:draft,published',
            'labels' => 'nullable|string',
            'source' => 'nullable|array',
            'source.*.name' => 'required_with:source|string',
            'source.*.url' => 'nullable|url',
        ]);

        $post->title = $validatedData['title'];
        // Don't update slug to keep links working, or only if needed. Let's keep slug as is.
        $post->content = $validatedData['content'];
        $post->status = $validatedData['status'];
        $post->source = $validatedData['source'] ?? null;

        if ($post->status === 'published' && !$post->published_at) {
            $post->published_at = now();
        }

        if ($request->hasFile('cover_image')) {
            $post->cover_image = $this->uploadImage(
                $request->file('cover_image'),
                'posts',
                fn($image) => $image->scaleDown(width: 800),
                $post->cover_image
            );
        }

        $post->save();

        // Handle Labels
        if (isset($validatedData['labels'])) {
            $labelNames = array_map('trim', explode(',', $validatedData['labels']));
            $labelIds = [];
            foreach ($labelNames as $name) {
                if (empty($name)) continue;
                $slug = Str::slug($name);
                $label = Label::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name]
                );
                $labelIds[] = $label->id;
            }
            $post->labels()->sync($labelIds);
        } else {
            $post->labels()->detach();
        }

        if (Auth::user()->hasRole('Super Admin')) {
            return redirect()->route('posts.index')->with('success', 'Tulisan berhasil diperbarui.');
        } else {
            return redirect()->route('kelola-tulisan.index')->with('success', 'Tulisan berhasil diperbarui.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if (!Auth::user()->hasAnyRole(['Super Admin', 'Penulis'])) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        if (!Auth::user()->hasRole('Super Admin') && $post->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $this->deleteImage($post->cover_image);

        $post->labels()->detach();
        $post->delete();

        return redirect()->back()->with('success', 'Tulisan berhasil dihapus.');
    }
}
