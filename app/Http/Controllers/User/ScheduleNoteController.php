<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ScheduleNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleNoteController extends Controller
{
    public function store(Request $request, $scheduleId)
    {
        $request->validate([
            'content' => 'required|string',
            'visibility' => 'required|in:Private,Public',
        ]);

        $note = new ScheduleNote();
        $note->schedule_id = $scheduleId;
        $note->user_id = Auth::id();
        $note->content = $request->content;
        $note->visibility = $request->visibility;

        if ($request->visibility === 'Public') {
            $note->status = 'Pending';
        } else {
            $note->status = 'Approved';
        }

        $note->save();

        $message = 'Catatan berhasil disimpan.';
        if ($request->visibility === 'Public') {
            $message .= ' Catatan publik Anda sedang menunggu moderasi.';
        }

        return redirect()->back()->with('success', $message);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $note = ScheduleNote::findOrFail($id);

        if ($note->visibility !== 'Public' && $note->user_id !== Auth::id()) {
            abort(403);
        }

        $note->content = $request->content;
        $note->save();

        return redirect()->back()->with('success', 'Catatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $note = ScheduleNote::findOrFail($id);

        if ($note->user_id !== Auth::id()) {
            abort(403);
        }

        $note->delete();

        return redirect()->back()->with('success', 'Catatan berhasil dihapus.');
    }
}
