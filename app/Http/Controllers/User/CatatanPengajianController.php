<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ScheduleNote;

class CatatanPengajianController extends Controller
{
    public function index()
    {
        return view('pages.user.catatan-pengajian.list');
    }

    public function show($id)
    {
        $note = ScheduleNote::with(['user', 'schedule.assembly'])
            ->where('visibility', 'Public')
            ->where('status', 'Approved')
            ->findOrFail($id);

        return view('pages.user.catatan-pengajian.detail', compact('note'));
    }
}
