<?php

namespace App\Http\Controllers;

use App\Livewire\HomeKitabUnggulan;
use App\Models\Book;
use App\Models\Library;
use App\Models\Specialization;
use App\Models\Teacher;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class BookController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        return view('pages.kitab.index');
    }

    public function create()
    {
        return view('pages.kitab.create', $this->formOptions() + [
            'book' => new Book,
            'teacherRows' => $this->teacherRows(null),
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $this->validateBook($request);
        $teachers = $this->validatedTeachers($request);

        $data = $this->bookAttributes($request, $validatedData);
        $data['slug'] = Book::generateSlug($validatedData['slug'] ?? $validatedData['title']);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->uploadImageWithThumbnail($request->file('cover_image'), 'books', 300, 800)['large'];
        }

        try {
            DB::transaction(function () use ($data, $teachers) {
                $book = Book::create($data);
                $this->syncTeachers($book, $teachers);
            });
        } catch (Throwable $e) {
            if (isset($data['cover_image'])) {
                $this->deleteImageWithThumbnail($data['cover_image']);
            }

            throw $e;
        }

        Cache::forget(HomeKitabUnggulan::CACHE_KEY);

        return redirect()->route('admin.kitab.index')->with('message', 'Kitab berhasil ditambahkan!');
    }

    public function edit(Book $kitab)
    {
        return view('pages.kitab.edit', $this->formOptions() + [
            'book' => $kitab,
            'teacherRows' => $this->teacherRows($kitab),
        ]);
    }

    public function update(Request $request, Book $kitab)
    {
        $validatedData = $this->validateBook($request, $kitab);
        $teachers = $this->validatedTeachers($request);

        $data = $this->bookAttributes($request, $validatedData);

        // Slug sengaja tidak mengikuti perubahan judul agar URL yang sudah terindeks tetap hidup.
        if (filled($validatedData['slug'] ?? null) && $validatedData['slug'] !== $kitab->slug) {
            $data['slug'] = Book::generateSlug($validatedData['slug'], $kitab->id);
        }

        $oldCover = $kitab->cover_image;

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->uploadImageWithThumbnail($request->file('cover_image'), 'books', 300, 800)['large'];
        }

        try {
            DB::transaction(function () use ($kitab, $data, $teachers) {
                $kitab->update($data);
                $this->syncTeachers($kitab, $teachers);
            });
        } catch (Throwable $e) {
            if (isset($data['cover_image'])) {
                $this->deleteImageWithThumbnail($data['cover_image']);
            }

            throw $e;
        }

        if (isset($data['cover_image']) && $oldCover) {
            $this->deleteImageWithThumbnail($oldCover);
        }

        Cache::forget(HomeKitabUnggulan::CACHE_KEY);

        return redirect()->route('admin.kitab.index')->with('message', 'Kitab berhasil diperbarui!');
    }

    public function destroy(Book $kitab)
    {
        $cover = $kitab->cover_image;

        $kitab->delete();

        if ($cover) {
            $this->deleteImageWithThumbnail($cover);
        }

        Cache::forget(HomeKitabUnggulan::CACHE_KEY);

        return redirect()->route('admin.kitab.index')->with('message', 'Kitab berhasil dihapus!');
    }

    private function validateBook(Request $request, ?Book $book = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'title_arabic' => 'nullable|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('books', 'slug')->ignore($book?->id)],
            'description' => 'nullable|string',
            'specialization_id' => 'nullable|integer|exists:specializations,id',
            'language' => ['nullable', Rule::in(array_keys(Book::LANGUAGES))],
            'publication_status' => ['nullable', Rule::in(array_keys(Book::PUBLICATION_STATUSES))],
            'publisher' => 'nullable|string|max:255',
            'external_url' => 'nullable|url:http,https|max:500',
            'library_id' => 'nullable|integer|exists:libraries,id',
            'cover_image' => 'nullable|image|max:2048',
            'teachers' => 'required|array|min:1',
            'teachers.*' => 'array',
            'teachers.*.teacher_id' => ['required', 'integer', function ($attribute, $value, $fail) {
                if (! Teacher::publiclyVisible()->whereKey($value)->exists()) {
                    $fail('Guru yang dipilih tidak ditemukan atau belum tayang.');
                }
            }],
            'teachers.*.role' => ['required', Rule::in(array_keys(Book::ROLES))],
        ], [
            'teachers.required' => 'Pilih minimal satu guru.',
            'teachers.*.teacher_id.required' => 'Setiap baris guru harus memilih guru.',
        ]);
    }

    /**
     * Urutan baris form menjadi sort_order. Pasangan (guru, peran) yang dobel
     * ditolak di sini agar tidak berakhir sebagai pelanggaran unique index.
     *
     * @return array<int, array{teacher_id: int, role: string}>
     */
    private function validatedTeachers(Request $request): array
    {
        $rows = array_values(array_map(fn (array $row) => [
            'teacher_id' => (int) $row['teacher_id'],
            'role' => $row['role'],
        ], $request->input('teachers')));

        $pairs = array_map(fn (array $row) => $row['teacher_id'].'|'.$row['role'], $rows);

        if (count($pairs) !== count(array_unique($pairs))) {
            throw ValidationException::withMessages([
                'teachers' => 'Guru yang sama tidak boleh memiliki peran yang sama dua kali.',
            ]);
        }

        return $rows;
    }

    private function bookAttributes(Request $request, array $validatedData): array
    {
        return [
            'title' => $validatedData['title'],
            'title_arabic' => $validatedData['title_arabic'] ?? null,
            'description' => filled($validatedData['description'] ?? null)
                ? clean($validatedData['description'], 'rich_text')
                : null,
            'specialization_id' => $validatedData['specialization_id'] ?? null,
            'language' => $validatedData['language'] ?? null,
            'publication_status' => $validatedData['publication_status'] ?? null,
            'publisher' => $validatedData['publisher'] ?? null,
            'external_url' => $validatedData['external_url'] ?? null,
            'library_id' => $validatedData['library_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
        ];
    }

    /**
     * Satu guru boleh punya beberapa peran pada kitab yang sama, sehingga
     * sync() yang berkunci teacher_id tidak bisa dipakai.
     */
    private function syncTeachers(Book $book, array $teachers): void
    {
        $book->teachers()->detach();

        foreach ($teachers as $index => $row) {
            $book->teachers()->attach($row['teacher_id'], [
                'role' => $row['role'],
                'sort_order' => $index,
            ]);
        }
    }

    private function formOptions(): array
    {
        return [
            'specializations' => Specialization::orderBy('name')->pluck('name', 'id'),
            'libraries' => Library::orderBy('title')->pluck('title', 'id'),
        ];
    }

    /**
     * Baris awal komponen pemilih guru: input lama saat validasi gagal,
     * relasi tersimpan saat edit, atau satu baris kosong.
     *
     * @return array<int, array{teacher_id: ?int, role: string}>
     */
    private function teacherRows(?Book $book): array
    {
        $old = old('teachers');

        if (is_array($old)) {
            return array_values(array_map(fn ($row) => [
                'teacher_id' => isset($row['teacher_id']) && $row['teacher_id'] !== '' ? (int) $row['teacher_id'] : null,
                'role' => $row['role'] ?? 'pengarang',
            ], $old));
        }

        if ($book?->exists) {
            return $book->teachers->map(fn (Teacher $teacher) => [
                'teacher_id' => $teacher->id,
                'role' => $teacher->pivot->role,
            ])->all();
        }

        return [['teacher_id' => null, 'role' => 'pengarang']];
    }
}
