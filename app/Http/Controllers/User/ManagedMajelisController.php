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
            'tipe' => 'nullable|string|in:Majelis Ta\'lim,Mesjid,Langgar,Musholla',
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
            // Delete old image
            if ($majelis->gambar) {
                Storage::delete($majelis->gambar);
                Storage::delete(str_replace('large', 'thumb', $majelis->gambar));
            }

            // Upload new image
            $file = $request->file('gambar');
            $filename = time() . '.' . $file->getClientOriginalExtension();

            // Simpan gambar original (large)
            $pathLarge = $file->storeAs('public/majelis/large', $filename);

            // Buat thumbnail
            $thumbPath = 'public/majelis/thumb/' . $filename;

            // Pastikan direktori thumb ada (storage link harus sudah jalan)
            // Menggunakan Intervention Image untuk resize
            $image = Image::read($file);

            // Resize logic: scale down to 800px width constraint, maintain aspect ratio
            $image->scaleDown(width: 800);

            // Save resized large image to storage
            Storage::put($pathLarge, $image->toWebp(80));

            // Untuk thumb, kita buat lebih kecil, misal 200px
            $imageThumb = Image::read($file);
            $imageThumb->scaleDown(width: 400);

            // Simpan manual ke storage (karena Intervention Image biasanya save ke local path)
            // Disini kita perlu simpan stream ke Storage facade agar kompatibel dengan filesystem driver (S3/Local)
            Storage::put($thumbPath, $imageThumb->toWebp(80)); // Simpan sebagai JPEG kualitas 80

            // Kita simpan path large ke database
            $data['gambar'] = $pathLarge;
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
