<?php

namespace App\Livewire\ScheduleNote;

use App\Models\ScheduleNote;
use Livewire\Component;

class CommentSection extends Component
{
    public ScheduleNote $note;
    public $body;

    public function mount(ScheduleNote $note)
    {
        $this->note = $note;
    }

    public function save()
    {
        abort_if(!auth()->check(), 403);

        $this->validate([
            'body' => 'required|min:3',
        ]);

        $this->note->comments()->create([
            'user_id' => auth()->id(),
            'body' => $this->body,
        ]);

        $this->body = '';
    }

    public function render()
    {
        return view('livewire.schedule-note.comment-section', [
            'comments' => $this->note->comments()
                ->with('user')
                ->latest()
                ->get(),
        ]);
    }
}
