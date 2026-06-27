<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Contribution;
use App\Models\ScheduleNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleNoteController extends Controller
{
    public function store(Request $request, $scheduleId)
    {
        if ($request->visibility === 'Public' && ! Auth::user()->hasRole('Kontributor')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Anda harus terdaftar sebagai Kontributor untuk berbagi catatan secara publik.');
        }

        $contentRules = ['required', 'string'];
        if ($request->visibility === 'Public') {
            $contentRules[] = 'min:50';
        }

        $request->validate([
            'content' => $contentRules,
            'visibility' => 'required|in:Private,Public',
        ]);

        $note = new ScheduleNote();
        $note->schedule_id = $scheduleId;
        $note->user_id = Auth::id();
        $note->content = $request->content;
        $note->visibility = $request->visibility;

        if ($request->visibility === 'Public') {
            $note->status = 'Pending';
            $note->contribution_status = 'pending';
        } else {
            $note->status = 'Approved';
        }

        $note->save();

        if ($request->visibility === 'Public') {
            Contribution::create([
                'user_id' => Auth::id(),
                'contributable_id' => $note->id,
                'contributable_type' => ScheduleNote::class,
                'points_earned' => 0,
            ]);
        }

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
