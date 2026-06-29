<?php

namespace App\Http\Controllers;

use App\Models\Library;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LibraryController extends Controller
{
    use ImageUploadTrait;

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
            'price' => 'nullable|integer|min:0|required_if:price_type,paid',
        ]);

        $dataToCreate = $validatedData;
        $dataToCreate['slug'] = Str::slug($validatedData['title']).'-'.Str::random(6);
        $dataToCreate['is_active'] = true;
        $dataToCreate['price'] = $validatedData['price_type'] === 'paid' ? $validatedData['price'] : null;

        if ($request->hasFile('file')) {
            $dataToCreate['file_path'] = $this->storePdf($request->file('file'), $validatedData['price_type']);
        }

        if ($request->hasFile('cover_image')) {
            $dataToCreate['cover_image'] = $this->handleImageUpload(
                $request->file('cover_image'),
                'libraries/covers',
                400,
                600
            );
        }

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
            'price' => 'nullable|integer|min:0|required_if:price_type,paid',
            'is_active' => 'sometimes|boolean',
        ]);

        $dataToUpdate = $validatedData;
        $newPriceType = $validatedData['price_type'];
        $oldDisk = $this->pdfDiskFor($library->price_type);
        $newDisk = $this->pdfDiskFor($newPriceType);

        if ($validatedData['title'] !== $library->title) {
            $dataToUpdate['slug'] = Str::slug($validatedData['title']).'-'.Str::random(6);
        }

        $dataToUpdate['price'] = $newPriceType === 'paid' ? $validatedData['price'] : null;

        if ($request->hasFile('file')) {
            if ($library->file_path && Storage::disk($oldDisk)->exists($library->file_path)) {
                Storage::disk($oldDisk)->delete($library->file_path);
            }

            $dataToUpdate['file_path'] = $this->storePdf($request->file('file'), $newPriceType);
        } elseif ($oldDisk !== $newDisk && $library->file_path && Storage::disk($oldDisk)->exists($library->file_path)) {
            // Status berbayar/gratis berubah tanpa ganti file: pindahkan file antar-disk.
            $newPath = $this->pdfFolderFor($newPriceType).'/'.basename($library->file_path);
            Storage::disk($newDisk)->put($newPath, Storage::disk($oldDisk)->get($library->file_path));
            Storage::disk($oldDisk)->delete($library->file_path);
            $dataToUpdate['file_path'] = $newPath;
        }

        if ($request->hasFile('cover_image')) {
            $this->deleteImage($library->cover_image);
            $dataToUpdate['cover_image'] = $this->handleImageUpload(
                $request->file('cover_image'),
                'libraries/covers',
                400,
                600
            );
        } else {
            unset($dataToUpdate['cover_image']);
        }

        unset($dataToUpdate['file']);

        // Checkbox tidak terkirim saat tidak dicentang.
        if (! $request->has('is_active')) {
            $dataToUpdate['is_active'] = 0;
        }

        $library->update($dataToUpdate);

        return redirect()->route('libraries.index')->with('message', 'Pustaka berhasil diperbarui!');
    }

    public function destroy(Library $library)
    {
        $disk = $this->pdfDiskFor($library->price_type);
        if ($library->file_path && Storage::disk($disk)->exists($library->file_path)) {
            Storage::disk($disk)->delete($library->file_path);
        }
        $this->deleteImage($library->cover_image);

        $library->delete();

        return redirect()->route('libraries.index')->with('message', 'Pustaka berhasil dihapus!');
    }

    /**
     * Simpan PDF ke disk yang sesuai: privat (local) untuk berbayar, publik untuk gratis.
     */
    private function storePdf(UploadedFile $file, string $priceType): string
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs($this->pdfFolderFor($priceType), $filename, $this->pdfDiskFor($priceType));
    }

    private function pdfDiskFor(string $priceType): string
    {
        return $priceType === 'paid' ? 'local' : 'public';
    }

    private function pdfFolderFor(string $priceType): string
    {
        return $priceType === 'paid' ? 'libraries/paid' : 'libraries/files';
    }
}
