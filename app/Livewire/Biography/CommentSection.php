<?php

namespace App\Livewire\Biography;

use App\Models\Teacher;
use Livewire\Component;

class CommentSection extends Component
{
    public Teacher $biography;
    public $body;

    public function mount(Teacher $biography)
    {
        $this->biography = $biography;
    }

    public function save()
    {
        $this->validate([
            'body' => 'required|min:3',
        ]);

        $this->biography->comments()->create([
            'user_id' => auth()->id(),
            'body' => $this->body,
        ]);

        $this->body = '';
    }

    public function render()
    {
        return view('livewire.biography.comment-section', [
            'comments' => $this->biography->comments()
                ->with('user')
                ->latest()
                ->get(),
        ]);
    }
}
