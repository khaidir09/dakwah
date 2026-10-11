<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function list()
    {
        return view('pages.user.kitab.list');
    }

    public function detail(Book $book)
    {
        abort_unless($book->isVisibleTo(Auth::user()), 404);

        $book->load([
            'teachers' => fn ($q) => $q->publiclyVisible(),
            'specialization',
            'library',
        ]);

        $teachersByRole = $book->teachers->groupBy('pivot.role');

        return view('pages.user.kitab.detail', compact('book', 'teachersByRole'));
    }
}
