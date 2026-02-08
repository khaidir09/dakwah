<?php

namespace App\Http\Controllers;

use App\Models\Library;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class LibraryController extends Controller
{
    public function index()
    {
        return view('pages.libraries.index');
    }

    public function create()
    {
        return view('pages.libraries.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'file' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'cover_image' => 'nullable|image|max:2048',
            'price_type' => 'required|in:free,paid',
        ]);

        $dataToCreate = $validatedData;
        $dataToCreate['slug'] = Str::slug($validatedData['title']) . '-' . Str::random(6);
        $dataToCreate['is_active'] = true;

        // Handle PDF Upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('libraries/files', $filename, 'public');
            $dataToCreate['file_path'] = $path;
        }

        // Handle Cover Image Upload
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = Str::uuid() . '.webp';

            // Resize/Crop 400x600 (Book ratio) and convert to WebP
            $thumb = Image::read($file)
                ->cover(400, 600)
                ->toWebp(80);

            Storage::disk('public')->put('libraries/covers/' . $filename, (string) $thumb);
            $dataToCreate['cover_image'] = 'libraries/covers/' . $filename;
        }

        // Remove 'file' from dataToCreate as it's not a column
        unset($dataToCreate['file']);

        Library::create($dataToCreate);

        return redirect()->route('libraries.index')->with('message', 'Pustaka berhasil ditambahkan!');
    }

    public function edit(Library $library)
    {
        return view('pages.libraries.edit', compact('library'));
    }

    public function update(Request $request, Library $library)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'file' => 'nullable|file|mimes:pdf|max:10240',
            'cover_image' => 'nullable|image|max:2048',
            'price_type' => 'required|in:free,paid',
            'is_active' => 'sometimes|boolean'
        ]);

        $dataToUpdate = $validatedData;

        // Only update slug if title changes
        if ($validatedData['title'] !== $library->title) {
            $dataToUpdate['slug'] = Str::slug($validatedData['title']) . '-' . Str::random(6);
        }

        // Handle PDF Upload
        if ($request->hasFile('file')) {
            // Delete old file
            if ($library->file_path && Storage::disk('public')->exists($library->file_path)) {
                Storage::disk('public')->delete($library->file_path);
            }

            $file = $request->file('file');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('libraries/files', $filename, 'public');
            $dataToUpdate['file_path'] = $path;
        }

        // Handle Cover Image Upload
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = Str::uuid() . '.webp';

            $thumb = Image::read($file)
                ->cover(400, 600)
                ->toWebp(80);

            Storage::disk('public')->put('libraries/covers/' . $filename, (string) $thumb);

            if ($library->cover_image && Storage::disk('public')->exists($library->cover_image)) {
                Storage::disk('public')->delete($library->cover_image);
            }

            $dataToUpdate['cover_image'] = 'libraries/covers/' . $filename;
        } else {
            unset($dataToUpdate['cover_image']);
        }

        unset($dataToUpdate['file']);

        // Handle is_active explicitly if not present (checkbox behavior) - actually boolean validation handles true/false/1/0.
        // If the checkbox is unchecked, it might not send anything.
        // In Laravel validation, 'sometimes|boolean' might skip if missing.
        // Usually creating a hidden input with 0 helps, or checking request->has.
        // Let's assume the form sends a value or we handle it.
        // For now, I'll assume the form handles it correctly or passed as 1/0.
        // If using standard HTML form submission for boolean:
        if (!$request->has('is_active')) {
            $dataToUpdate['is_active'] = 0;
        }

        $library->update($dataToUpdate);

        return redirect()->route('libraries.index')->with('message', 'Pustaka berhasil diperbarui!');
    }

    public function destroy(Library $library)
    {
        if ($library->file_path && Storage::disk('public')->exists($library->file_path)) {
            Storage::disk('public')->delete($library->file_path);
        }
        if ($library->cover_image && Storage::disk('public')->exists($library->cover_image)) {
            Storage::disk('public')->delete($library->cover_image);
        }

        $library->delete();

        return redirect()->route('libraries.index')->with('message', 'Pustaka berhasil dihapus!');
    }
}
