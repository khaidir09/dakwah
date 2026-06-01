<?php

namespace App\Http\Controllers\User;

use App\Models\Foundation;
use App\Models\ScientificArticle;
use Illuminate\Http\Request;
use App\Http\Requests\StoreScientificArticleRequest;
use App\Http\Requests\UpdateScientificArticleRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManagedFoundationController extends Controller
{
    /**
     * Show the form for editing the specified foundation.
     */
    public function edit($id)
    {
        // Ensure the user is associated with this foundation
        $foundation = Auth::user()->foundations()->findOrFail($id);

        return view('pages.user.kelola-foundation.edit', compact('foundation'));
    }

    /**
     * Update the specified foundation in storage.
     */
    public function update(Request $request, $id)
    {
        $foundation = Auth::user()->foundations()->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'website_url' => 'nullable|url|max:255',
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'website_url']);

        if ($request->hasFile('logo_path')) {
            // Delete old logo
            if ($foundation->logo_path) {
                Storage::delete($foundation->logo_path);
            }

            // Upload new logo
            $file = $request->file('logo_path');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/foundations/logos', $filename);

            $data['logo_path'] = $path;
        }

        $foundation->update($data);

        return redirect()->back()->with('status', 'Data yayasan berhasil diperbarui');
    }

    /**
     * Display a listing of scientific articles.
     */
    public function listArticles()
    {
        return view('pages.user.kelola-foundation.artikel');
    }

    /**
     * Show the form for creating a new scientific article.
     */
    public function createArticle()
    {
        $foundations = Auth::user()->foundations;

        if ($foundations->isEmpty()) {
            abort(403, 'Anda tidak memiliki akses ke yayasan manapun.');
        }

        return view('pages.user.kelola-foundation.tambah-artikel', compact('foundations'));
    }

    /**
     * Store a newly created scientific article in storage.
     */
    public function storeArticle(StoreScientificArticleRequest $request)
    {
        // Ensure user belongs to the foundation
        if (!Auth::user()->foundations()->where('foundations.id', $request->foundation_id)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke yayasan ini.');
        }

        $data = $request->except(['citations', 'bibliography']);
        $data['slug'] = Str::slug($request->title) . '-' . time();

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->handleFileUpload($request, 'cover_image', 'public/articles/covers');
        }

        if ($request->hasFile('file_path')) {
            $data['file_path'] = $this->handleFileUpload($request, 'file_path', 'public/articles/files');
        }

        $article = ScientificArticle::create($data);

        // Save Citations
        if ($request->has('citations')) {
            $article->citations()->createMany($request->citations);
        }

        // Save Bibliography
        if ($request->has('bibliography')) {
            $article->bibliography()->createMany($request->bibliography);
        }

        return redirect()->route('kelola-artikel.index')->with('message', 'Artikel berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified scientific article.
     */
    public function editArticle($id)
    {
        $article = ScientificArticle::with(['citations', 'bibliography'])->findOrFail($id);

        // Authorization check: User must belong to the foundation of the article
        if (!Auth::user()->foundations()->where('foundations.id', $article->foundation_id)->exists()) {
            abort(403, 'Anda tidak berhak mengedit artikel ini.');
        }

        $foundations = Auth::user()->foundations;

        return view('pages.user.kelola-foundation.edit-artikel', compact('article', 'foundations'));
    }

    /**
     * Update the specified scientific article in storage.
     */
    public function updateArticle(UpdateScientificArticleRequest $request, $id)
    {
        $article = ScientificArticle::findOrFail($id);

        // Authorization check
        if (!Auth::user()->foundations()->where('foundations.id', $article->foundation_id)->exists()) {
            abort(403, 'Anda tidak berhak mengupdate artikel ini.');
        }

        // Ensure user belongs to the new foundation (if changed)
        if (!Auth::user()->foundations()->where('foundations.id', $request->foundation_id)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke yayasan ini.');
        }

        $data = $request->except(['cover_image', 'file_path', 'citations', 'bibliography']);

        if ($article->title !== $request->title) {
            $data['slug'] = Str::slug($request->title) . '-' . time();
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->handleFileUpload($request, 'cover_image', 'public/articles/covers', $article->cover_image);
        }

        if ($request->hasFile('file_path')) {
            $data['file_path'] = $this->handleFileUpload($request, 'file_path', 'public/articles/files', $article->file_path);
        }

        $article->update($data);

        // Sync Citations
        $article->citations()->delete();
        if ($request->has('citations')) {
            $article->citations()->createMany($request->citations);
        }

        // Sync Bibliography
        $article->bibliography()->delete();
        if ($request->has('bibliography')) {
            $article->bibliography()->createMany($request->bibliography);
        }

        return redirect()->route('kelola-artikel.index')->with('message', 'Artikel berhasil diperbarui!');
    }

    /**
     * Handle file upload.
     */
    private function handleFileUpload(Request $request, string $fileKey, string $storagePath, ?string $oldPath = null): string
    {
        if ($oldPath) {
            Storage::delete($oldPath);
        }

        $file = $request->file($fileKey);
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($storagePath, $filename);
    }

    /**
     * Remove the specified scientific article from storage.
     */
    public function destroyArticle($id)
    {
        $article = ScientificArticle::findOrFail($id);

        if (!Auth::user()->foundations()->where('foundations.id', $article->foundation_id)->exists()) {
            abort(403, 'Anda tidak berhak menghapus artikel ini.');
        }

        if ($article->cover_image) {
            Storage::delete($article->cover_image);
        }

        if ($article->file_path) {
            Storage::delete($article->file_path);
        }

        $article->delete();

        return redirect()->route('kelola-artikel.index')->with('message', 'Artikel berhasil dihapus!');
    }
}
