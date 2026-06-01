<?php

namespace App\Http\Controllers;

use App\Models\Foundation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use App\Traits\HasImageUploads;

class FoundationController extends Controller
{
    use HasImageUploads;
    public function index()
    {
        return view('pages.foundations.index');
    }

    public function create()
    {
        return view('pages.foundations.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'website_url' => 'required|url|max:255',
            'logo' => 'required|image|max:2048', // distinct input name from column
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $dataToCreate = [
            'name' => $validatedData['name'],
            'website_url' => $validatedData['website_url'],
        ];

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            // Resize to standard logo size (e.g., 200x200 or maintain aspect ratio)
            // Assuming square or fit
            $dataToCreate['logo_path'] = $this->uploadImage(
                $request->file('logo'),
                'foundations/logos',
                fn($image) => $image->scaleDown(width: 300)
            );
        }

        $foundation = Foundation::create($dataToCreate);

        if ($request->has('user_ids')) {
            $foundation->users()->sync($request->user_ids);
        }

        return redirect()->route('foundations.index')->with('message', 'Foundation created successfully.');
    }

    public function edit(Foundation $foundation)
    {
        return view('pages.foundations.edit', compact('foundation'));
    }

    public function update(Request $request, Foundation $foundation)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'website_url' => 'required|url|max:255',
            'logo' => 'nullable|image|max:2048',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $dataToUpdate = [
            'name' => $validatedData['name'],
            'website_url' => $validatedData['website_url'],
        ];

        // Handle Logo Upload
        if ($request->hasFile('logo')) {
            $dataToUpdate['logo_path'] = $this->uploadImage(
                $request->file('logo'),
                'foundations/logos',
                fn($image) => $image->scaleDown(width: 300),
                $foundation->logo_path
            );
        }

        $foundation->update($dataToUpdate);

        // Sync users (default to empty if not present)
        $foundation->users()->sync($request->input('user_ids', []));

        return redirect()->route('foundations.index')->with('message', 'Foundation updated successfully.');
    }

    public function destroy(Foundation $foundation)
    {
        $this->deleteImage($foundation->logo_path);

        $foundation->delete();
        return redirect()->route('foundations.index')->with('message', 'Foundation deleted successfully.');
    }
}
