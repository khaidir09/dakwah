<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Library;
use App\Models\LibraryPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LibraryController extends Controller
{
    public function list()
    {
        return view('pages.user.library.index');
    }

    public function detail(Library $library)
    {
        $library->increment('visit_count');

        return view('pages.user.library.detail', compact('library'));
    }

    /**
     * Catat permintaan pembelian (pending) lalu arahkan ke WhatsApp admin untuk konfirmasi.
     */
    public function purchase(Library $library)
    {
        abort_unless($library->isPaid(), 404);

        $user = Auth::user();

        $purchase = $library->purchases()
            ->where('user_id', $user->id)
            ->whereIn('status', [LibraryPurchase::STATUS_PENDING, LibraryPurchase::STATUS_ACTIVE])
            ->first();

        // Sudah punya akses aktif: tidak perlu beli lagi.
        if ($purchase && $purchase->status === LibraryPurchase::STATUS_ACTIVE) {
            return redirect()->route('pustaka-detail', $library)
                ->with('message', 'Anda sudah memiliki akses ke pustaka ini.');
        }

        // Buat permintaan baru hanya bila belum ada yang pending.
        $purchase ??= $library->purchases()->create([
            'user_id' => $user->id,
            'status' => LibraryPurchase::STATUS_PENDING,
            'price' => $library->price,
        ]);

        $adminNumber = config('services.whatsapp.admin_number');

        if (empty($adminNumber)) {
            return redirect()->route('pustaka-detail', $library)
                ->with('message', 'Permintaan pembelian tercatat. Admin akan menghubungi Anda untuk konfirmasi pembayaran.');
        }

        return redirect()->away($this->whatsappUrl($adminNumber, $library, $purchase, $user->name));
    }

    /**
     * Halaman penampil dokumen in-app (PDF.js, render ke canvas) — tanpa toolbar
     * unduh/print bawaan browser. Byte PDF diambil terpisah via stream() lewat XHR.
     */
    public function read(Library $library)
    {
        if (! $library->isAccessibleBy(Auth::user())) {
            abort(403);
        }

        if (! $library->file_path) {
            abort(404);
        }

        return view('pages.user.library.read', compact('library'));
    }

    /**
     * Salurkan byte PDF untuk penampil. Hanya menerima permintaan XHR (dari PDF.js)
     * agar URL ini tidak bisa dibuka langsung sebagai tab PDF yang bisa diunduh.
     * Gratis terbuka untuk user terautentikasi; berbayar hanya admin/pemilik akses aktif.
     */
    public function stream(Request $request, Library $library)
    {
        if (! $library->isAccessibleBy(Auth::user())) {
            abort(403);
        }

        if ($request->header('X-Requested-With') !== 'XMLHttpRequest') {
            abort(403);
        }

        $disk = $library->isPaid() ? 'local' : 'public';

        if (! $library->file_path || ! Storage::disk($disk)->exists($library->file_path)) {
            abort(404);
        }

        return Storage::disk($disk)->response(
            $library->file_path,
            $library->slug.'.pdf',
            [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ],
            'inline'
        );
    }

    /**
     * Riwayat pembelian milik user yang sedang login ("Pustaka Saya").
     */
    public function myLibraries()
    {
        $purchases = Auth::user()->libraryPurchases()
            ->with('library')
            ->latest()
            ->paginate(12);

        return view('pages.user.library.my-libraries', compact('purchases'));
    }

    private function whatsappUrl(string $adminNumber, Library $library, LibraryPurchase $purchase, string $userName): string
    {
        $message = "Assalamu'alaikum, saya ingin membeli pustaka berikut:\n"
            ."Judul: {$library->title}\n"
            .'Harga: Rp'.number_format((int) $purchase->price, 0, ',', '.')."\n"
            ."Nama: {$userName}\n"
            ."Kode permintaan: #{$purchase->id}";

        return 'https://wa.me/'.$adminNumber.'?text='.rawurlencode($message);
    }
}
