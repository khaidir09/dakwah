<?php

namespace App\Http\Controllers;

use App\Models\Foundation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class FoundationController extends Controller
{
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
            $file = $request->file('logo');
            $filename = Str::uuid() . '.webp';

            // Resize to standard logo size (e.g., 200x200 or maintain aspect ratio)
            // Assuming square or fit
            $thumb = Image::read($file)
                ->scaleDown(width: 300)
                ->toWebp(80);

            Storage::disk('public')->put('foundations/logos/' . $filename, (string) $thumb);
            $dataToCreate['logo_path'] = 'foundations/logos/' . $filename;
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
            // Delete old logo
            if ($foundation->logo_path && Storage::disk('public')->exists($foundation->logo_path)) {
                Storage::disk('public')->delete($foundation->logo_path);
            }

            $file = $request->file('logo');
            $filename = Str::uuid() . '.webp';

            $thumb = Image::read($file)
                ->scaleDown(width: 300)
                ->toWebp(80);

            Storage::disk('public')->put('foundations/logos/' . $filename, (string) $thumb);
            $dataToUpdate['logo_path'] = 'foundations/logos/' . $filename;
        }

        $foundation->update($dataToUpdate);

        // Sync users (default to empty if not present)
        $foundation->users()->sync($request->input('user_ids', []));

        return redirect()->route('foundations.index')->with('message', 'Foundation updated successfully.');
    }

    public function destroy(Foundation $foundation)
    {
        if ($foundation->logo_path && Storage::disk('public')->exists($foundation->logo_path)) {
            Storage::disk('public')->delete($foundation->logo_path);
        }

        $foundation->delete();
        return redirect()->route('foundations.index')->with('message', 'Foundation deleted successfully.');
    }
}
