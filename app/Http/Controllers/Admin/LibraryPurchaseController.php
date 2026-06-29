<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryPurchase;
use App\Services\OneSignalService;
use Illuminate\Http\Request;

class LibraryPurchaseController extends Controller
{
    public function __construct(protected OneSignalService $oneSignal) {}

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $purchases = LibraryPurchase::with(['user', 'library', 'verifier'])
            ->when(
                in_array($status, ['pending', 'active', 'rejected'], true),
                fn ($q) => $q->where('status', $status)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.library-purchases.index', compact('purchases', 'status'));
    }

    public function activate(Request $request, LibraryPurchase $purchase)
    {
        // Hanya aktifkan yang masih pending agar tidak menggandakan notifikasi.
        if ($purchase->status !== LibraryPurchase::STATUS_ACTIVE) {
            $purchase->update([
                'status' => LibraryPurchase::STATUS_ACTIVE,
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
            ]);

            $this->notifyActivated($purchase);
        }

        return redirect()->route('admin.library-purchases.index')
            ->with('message', 'Akses pustaka diaktifkan.');
    }

    public function reject(Request $request, LibraryPurchase $purchase)
    {
        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $purchase->update([
            'status' => LibraryPurchase::STATUS_REJECTED,
            'admin_note' => $validated['admin_note'] ?? null,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return redirect()->route('admin.library-purchases.index')
            ->with('message', 'Permintaan pembelian ditolak.');
    }

    private function notifyActivated(LibraryPurchase $purchase): void
    {
        $library = $purchase->library;

        // Kegagalan push tidak boleh menggagalkan aktivasi; service sudah menulis log sendiri.
        $this->oneSignal->sendNotification(
            'Akses Pustaka Aktif',
            "Pembelian \"{$library->title}\" telah aktif. Selamat membaca.",
            [$purchase->user_id],
            route('pustaka-detail', $library),
        );
    }
}
