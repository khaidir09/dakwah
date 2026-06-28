<?php

namespace App\Http\Controllers;

use App\Models\RewardClaim;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RewardProofController extends Controller
{
    /**
     * Menyajikan bukti transfer dari disk privat. Hanya pemilik klaim atau admin
     * yang berwenang (FR-08, AZ-04).
     */
    public function show(RewardClaim $claim)
    {
        $user = Auth::user();

        abort_unless($claim->user_id === $user->id || $user->hasRole('Super Admin'), 403);
        abort_if(empty($claim->transfer_proof_path), 404);

        return Storage::disk('local')->response($claim->transfer_proof_path);
    }
}
