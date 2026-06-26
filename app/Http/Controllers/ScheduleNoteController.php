<?php

namespace App\Http\Controllers;

use App\Models\ScheduleNote;
use App\Services\KhidmahService;
use Illuminate\Http\Request;

class ScheduleNoteController extends Controller
{
    public function __construct(private KhidmahService $khidmah) {}

    public function index()
    {
        $notes = ScheduleNote::with(['user', 'schedule'])->where('visibility', 'Public')->latest()->paginate(20);
        return view('pages.admin.schedule-notes.index', compact('notes'));
    }

    public function approve($id)
    {
        $note = ScheduleNote::findOrFail($id);
        $note->status = 'Approved';
        $note->save();

        $this->khidmah->approve($note, 'catatan_pengajian', 'Catatan Pengajian');

        return redirect()->back()->with('success', 'Catatan disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $note = ScheduleNote::findOrFail($id);
        $note->status = 'Rejected';
        $note->save();

        $reason = $request->input('reason', 'Tidak memenuhi standar catatan pengajian.');
        $this->khidmah->reject($note, 'Catatan Pengajian', $reason);

        return redirect()->back()->with('success', 'Catatan ditolak.');
    }
}
