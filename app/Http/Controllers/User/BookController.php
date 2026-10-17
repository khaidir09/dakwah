<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function list()
    {
        return view('pages.user.kitab.list', [
            'usulanUrl' => $this->usulanKitabUrl(),
        ]);
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

    /**
     * Katalog kitab dikurasi admin, jadi usulan dari masyarakat dikirim lewat
     * WhatsApp admin (bukan alur Kontributor). Null bila nomor admin belum diatur.
     */
    private function usulanKitabUrl(): ?string
    {
        $adminNumber = config('services.whatsapp.admin_number');

        if (empty($adminNumber)) {
            return null;
        }

        $message = implode("\n", [
            "Assalamu'alaikum, saya ingin mengusulkan kitab/karya ulama untuk Syaikhuna.",
            'Judul kitab:',
            'Nama pengarang:',
            'Bidang ilmu:',
            'Keterangan (penerbit, tahun, keberadaan naskah, dll.):',
        ]);

        return 'https://wa.me/'.$adminNumber.'?text='.rawurlencode($message);
    }
}
