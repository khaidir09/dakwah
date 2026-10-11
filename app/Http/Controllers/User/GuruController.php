<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Schedule;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

class GuruController extends Controller
{
    public function list()
    {
        // Daftar guru dirender oleh <livewire:list-guru />, yang memfilter
        // sendiri lewat Teacher::publiclyVisible().
        return view('pages/user/guru/list');
    }

    public function detail(Teacher $teacher)
    {
        abort_unless($teacher->isVisibleTo(Auth::user()), 404);

        $teacher->load('contributor');
        // CASE, bukan FIELD(): FIELD hanya ada di MySQL sehingga halaman ini tidak bisa diuji.
        $urutanHari = "CASE hari
            WHEN 'Senin' THEN 1
            WHEN 'Selasa' THEN 2
            WHEN 'Rabu' THEN 3
            WHEN 'Kamis' THEN 4
            WHEN 'Jumat' THEN 5
            WHEN 'Sabtu' THEN 6
            WHEN 'Minggu' THEN 7
            ELSE 8 END";

        $schedules = Schedule::with('teacher')
            ->publiclyVisible()
            ->where('teacher_id', $teacher->id)
            ->orderByRaw($urutanHari)
            ->get();

        // Satu kitab bisa muncul lebih dari sekali bila guru memegang beberapa peran di dalamnya.
        $books = $teacher->books()
            ->active()
            ->with('specialization')
            ->orderByDesc('books.created_at')
            ->get()
            ->groupBy('id')
            ->map(fn ($rows) => [
                'book' => $rows->first(),
                'roles' => $rows->map(fn ($book) => Book::ROLES[$book->pivot->role] ?? $book->pivot->role)->unique()->implode(', '),
            ])
            ->values();

        return view('pages/user/guru/detail', compact('teacher', 'schedules', 'books'));
    }
}
