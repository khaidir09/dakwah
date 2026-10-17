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

        return view('pages.user.library.read', [
            'library' => $library,
            'streamUrl' => route('pustaka-stream', $library),
            'isPreview' => false,
        ]);
    }

    /**
     * Penampil pratinjau gratis (mis. Bab I) pustaka berbayar, terbuka tanpa login.
     * Pemilik akses langsung diarahkan ke versi lengkap; admin tetap bisa melihat pratinjau.
     */
    public function preview(Library $library)
    {
        $user = Auth::user();
        $isAdmin = (bool) $user?->hasRole('Super Admin');

        abort_unless($library->hasPreview() && ($library->is_active || $isAdmin), 404);

        if (! $isAdmin && $library->file_path && $library->isAccessibleBy($user)) {
            return redirect()->route('pustaka-read', $library);
        }

        return view('pages.user.library.read', [
            'library' => $library,
            'streamUrl' => route('pustaka-preview-stream', $library),
            'isPreview' => true,
        ]);
    }

    public function streamPreview(Request $request, Library $library)
    {
        abort_unless($library->hasPreview() && ($library->is_active || Auth::user()?->hasRole('Super Admin')), 404);

        return $this->pdfResponse($request, 'local', $library->preview_file_path, $library->slug.'-pratinjau.pdf');
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

        return $this->pdfResponse($request, $library->isPaid() ? 'local' : 'public', $library->file_path, $library->slug.'.pdf');
    }

    /**
     * Hanya menerima XHR (dari PDF.js) agar URL tidak bisa dibuka langsung sebagai tab PDF.
     */
    private function pdfResponse(Request $request, string $disk, ?string $path, string $filename)
    {
        if ($request->header('X-Requested-With') !== 'XMLHttpRequest') {
            abort(403);
        }

        if (! $path || ! Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        return Storage::disk($disk)->response(
            $path,
            $filename,
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
        // Harga yang disembunyikan baru disampaikan admin saat membalas chat.
        $message = "Assalamu'alaikum, saya ingin membeli pustaka berikut:\n"
            ."Judul: {$library->title}\n"
            .($library->show_price ? 'Harga: Rp'.number_format((int) $purchase->price, 0, ',', '.')."\n" : '')
            ."Nama: {$userName}\n"
            ."Kode permintaan: #{$purchase->id}";

        return 'https://wa.me/'.$adminNumber.'?text='.rawurlencode($message);
    }
}
