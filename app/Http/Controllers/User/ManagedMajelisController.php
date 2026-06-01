<?php

namespace App\Http\Controllers\User;

use App\Models\Teacher;
use App\Models\Assembly;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use App\Traits\HandlesImageUploads;

class ManagedMajelisController extends Controller
{
    use HandlesImageUploads;
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
            // A. Delete old image
            if ($majelis->gambar) {
                $this->deleteImageWithThumbnail($majelis->gambar);
            }

            // B. Upload new image
            $paths = $this->uploadImageWithThumbnail($request->file('gambar'), 'majelis');

            // Kita simpan path large ke database
            $data['gambar'] = $paths['large'];
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
