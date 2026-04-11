<?php

namespace App\Http\Controllers;

use App\Models\ScheduleNote;
use Illuminate\Http\Request;

class ScheduleNoteController extends Controller
{
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

        return redirect()->back()->with('success', 'Catatan disetujui.');
    }

    public function reject($id)
    {
        $note = ScheduleNote::findOrFail($id);
        $note->status = 'Rejected';
        $note->save();

        return redirect()->back()->with('success', 'Catatan ditolak.');
    }
}
