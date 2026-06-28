<?php

namespace App\Http\Controllers\User;

use App\Exceptions\RewardClaimException;
use App\Http\Controllers\Controller;
use App\Services\RewardClaimService;
use Illuminate\Http\Request;

class RewardClaimController extends Controller
{
    public function __construct(protected RewardClaimService $service) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ewallet_type' => 'required|in:Dana,GoPay,OVO,ShopeePay',
            'ewallet_number' => 'required|string|max:30',
            'ewallet_holder_name' => 'required|string|max:100',
        ]);

        try {
            $this->service->submit($request->user(), $validated);
        } catch (RewardClaimException $e) {
            return redirect()->route('kontributor.saya')->with('error', $e->getMessage());
        }

        return redirect()->route('kontributor.saya')
            ->with('success', 'Pengajuan klaim reward berhasil dikirim dan sedang menunggu diproses admin.');
    }
}
