<?php

namespace App\Livewire\Post;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $query = Post::with(['user', 'labels']);

        if (!$user->hasRole('Super Admin')) {
            $query->where('user_id', $user->id);
        }

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        return view('livewire.post.index', [
            'posts' => $query->latest()->paginate(10)
        ]);
    }

    public function delete($id)
    {
        $post = Post::findOrFail($id);

        if (!Auth::user()->hasRole('Super Admin') && $post->user_id !== Auth::id()) {
            return;
        }

        if ($post->cover_image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($post->cover_image);
        }
        $post->labels()->detach();
        $post->delete();

        session()->flash('success', 'Tulisan berhasil dihapus.');
    }
}
