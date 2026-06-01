<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ScheduleNote;
use App\Models\ScheduleNoteComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleNoteCommentController extends Controller
{
    public function store(Request $request, $noteId)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $note = ScheduleNote::findOrFail($noteId);

        if ($note->visibility !== 'Public') {
            abort(403, 'Anda hanya dapat memberikan komentar pada catatan publik.');
        }

        ScheduleNoteComment::create([
            'schedule_note_id' => $noteId,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return redirect()->back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $comment = ScheduleNoteComment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Komentar berhasil dihapus.');
    }
}
