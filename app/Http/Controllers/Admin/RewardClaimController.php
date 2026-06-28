<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardClaim;
use App\Services\RewardClaimService;
use Illuminate\Http\Request;

class RewardClaimController extends Controller
{
    public function __construct(protected RewardClaimService $service) {}

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $claims = RewardClaim::with(['user', 'processor'])
            ->when(in_array($status, ['pending', 'paid', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.reward-klaim.index', compact('claims', 'status'));
    }

    public function markPaid(Request $request, RewardClaim $claim)
    {
        $validated = $request->validate([
            'transferred_at' => 'required|date',
            'transfer_proof' => 'required|image|mimes:webp,jpg,jpeg,png|max:2048',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $this->service->markPaid($claim, [
            'transferred_at' => $validated['transferred_at'],
            'transfer_proof' => $validated['transfer_proof'],
            'admin_note' => $validated['admin_note'] ?? null,
            'processed_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.reward-klaim.index')
            ->with('message', 'Klaim ditandai sudah ditransfer.');
    }

    public function reject(Request $request, RewardClaim $claim)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $this->service->reject($claim, $validated['rejection_reason'], $request->user()->id);

        return redirect()->route('admin.reward-klaim.index')
            ->with('message', 'Klaim ditolak.');
    }
}
