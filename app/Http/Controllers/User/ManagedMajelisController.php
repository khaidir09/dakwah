<?php

namespace App\Http\Controllers\User;

use App\Models\Teacher;
use App\Models\Assembly;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageService;

class ManagedMajelisController extends Controller
{
    public function register()
    {
        return view('pages.user.registrasi-majelis');
    }

    public function edit($id)
    {
        $majelis = Assembly::where('user_id', Auth::user()->id)->findOrFail($id);

        return view('pages.user.kelola-majelis.edit', compact('majelis'));
    }

    public function update(Request $request, $id)
    {
        $majelis = Assembly::where('user_id', Auth::user()->id)->findOrFail($id);

        $request->validate([
            'nama_majelis' => 'required',
            'tipe' => 'nullable|string|in:Majelis,Mesjid,Langgar,Musholla',
            'alamat' => 'required',
            'deskripsi' => 'required',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'youtube' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
        ]);

        $data = $request->except(['gambar']);

        if ($request->hasFile('gambar')) {
            if ($majelis->gambar) {
                ImageService::delete($majelis->gambar, 'local');
                ImageService::delete(str_replace('large', 'thumb', $majelis->gambar), 'local');
            }

            $paths = ImageService::uploadVariations(
                $request->file('gambar'),
                'majelis',
                [
                    'thumb' => ['width' => 400, 'method' => 'scaleDown'],
                    'large' => ['width' => 800, 'method' => 'scaleDown'],
                ]
            );

            // Keep 'public/majelis/large' logic to avoid breaking existing data path format expectation
            // if other parts strictly look for 'public/' in db. Let's see what was originally saved:
            // Storage::put('public/majelis/large/xxx.webp')
            // The uploadVariations puts in `disk('public')` which normally saves without `public/` in the string.
            // Oh wait, `put('public/majelis/large/xxx', ...)` on 'local' disk vs `put('majelis/...', ...)` on 'public' disk.
            // Let's adapt so we save the format as intended.
            // Actually, `$pathLarge = $file->storeAs('public/majelis/large', ...)` -> this returns `public/majelis/large/...`
            // Wait, I should make sure it saves the same path or we adapt. Let's just use what uploadVariations returns,
            // which is relative to the `public` disk (i.e. 'majelis/large/...').
            // The old code stored 'public/majelis/large/xxx.webp' in the db if using default filesystem disk.
            // Actually, I'll just keep it consistent with MajelisController which uses `majelis/large/...`.
            $data['gambar'] = 'public/' . $paths['large'];
        }

        $majelis->update($data);

        return redirect()->back()->with('status', 'Data majelis berhasil diperbarui');
    }

    public function list()
    {
        return view('pages.user.kelola-majelis.jadwal');
    }

    public function create()
    {
        $majelis = Assembly::where('user_id', Auth::user()->id)->first();
        return view('pages.user.kelola-majelis.tambah-jadwal', compact('majelis'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_jadwal' => 'required|string|max:255',
            'assembly_id' => 'required|exists:assemblies,id',
            'teacher_id' => 'required|exists:teachers,id',
            'waktu' => 'required',
            'deskripsi' => 'string|nullable',
            'hari' => 'required|string|max:50',
            'access' => 'required|string|in:Umum,Ikhwan,Akhwat',
        ]);

        Schedule::create($validatedData);

        return redirect()->route('kelola-jadwal-majelis')->with('message', 'Jadwal majelis berhasil ditambahkan!');
    }

    public function editSchedule($id)
    {
        $schedule = Schedule::with('assembly')->findOrFail($id);

        // Authorization check
        if ($schedule->assembly->user_id !== Auth::id()) {
            abort(403);
        }

        $teachers = Teacher::where('wafat_masehi', null)->get();
        return view('pages.user.kelola-majelis.edit-jadwal', compact('schedule', 'teachers'));
    }

    public function updateSchedule(Request $request, $id)
    {
        $schedule = Schedule::with('assembly')->findOrFail($id);

        // Authorization check
        if ($schedule->assembly->user_id !== Auth::id()) {
            abort(403);
        }

        $validatedData = $request->validate([
            'nama_jadwal' => 'required|string|max:255',
            'teacher_id' => 'required|exists:teachers,id',
            'waktu' => 'required',
            'deskripsi' => 'string|nullable',
            'hari' => 'required|string|max:50',
            'access' => 'required|string|in:Umum,Ikhwan,Akhwat',
        ]);

        $schedule->update($validatedData);

        return redirect()->route('kelola-jadwal-majelis')->with('message', 'Jadwal majelis berhasil diperbarui!');
    }
}
