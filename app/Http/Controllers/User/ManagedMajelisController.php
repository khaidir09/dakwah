<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Assembly;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\Province;
use Intervention\Image\Laravel\Facades\Image;

class ManagedMajelisController extends Controller
{
    public function index()
    {
        $assemblies = Assembly::where('user_id', auth()->id())->latest()->get();
        return view('pages.user.majelis-ku.index', compact('assemblies'));
    }

    public function edit($id)
    {
        $majelis = Assembly::where('user_id', auth()->id())->findOrFail($id);
        $teachers = Teacher::all();
        $provinces = Province::pluck('name', 'code');

        return view('pages.user.majelis-ku.edit', compact('majelis', 'teachers', 'provinces'));
    }

    public function update(Request $request, $id)
    {
        $majelis = Assembly::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'nama_majelis' => 'required',
            'teacher_id' => 'required',
            'alamat' => 'required',
            'deskripsi' => 'required',
            'province' => 'required',
            'city' => 'required',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except(['gambar', 'province', 'city', 'district', 'village']);

        $data['province_code'] = $request->province;
        $data['city_code'] = $request->city;
        $data['district_code'] = $request->district;
        $data['village_code'] = $request->village;

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
            Storage::put($pathLarge, $image->toJpeg(80));

            // Untuk thumb, kita buat lebih kecil, misal 200px
            $imageThumb = Image::read($file);
            $imageThumb->scaleDown(width: 400);

            // Simpan manual ke storage (karena Intervention Image biasanya save ke local path)
            // Disini kita perlu simpan stream ke Storage facade agar kompatibel dengan filesystem driver (S3/Local)
            Storage::put($thumbPath, $imageThumb->toJpeg(80)); // Simpan sebagai JPEG kualitas 80

            // Kita simpan path large ke database
            $data['gambar'] = $pathLarge;
        }

        $majelis->update($data);

        return redirect()->route('majelis-ku.index')->with('status', 'Data majelis berhasil diperbarui');
    }
}
