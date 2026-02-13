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

    public $confirmingDeletion = false;
    public $post_id_to_delete;

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

    public function confirmDelete($postId)
    {
        $this->post_id_to_delete = $postId; // Simpan ID
        $this->confirmingDeletion = true; // Buka modal
    }

    public function deletePost()
    {
        // Pastikan ID ada
        if ($this->post_id_to_delete) {
            $post = Post::find($this->post_id_to_delete);

            if ($post) {
                if ($post->cover_image) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($post->cover_image);
                }
                $post->labels()->detach();
                $post->delete();
                // Kirim pesan sukses (akan kita tampilkan di view)
                session()->flash('success', 'Tulisan berhasil dihapus.');
            }
        }

        // Tutup modal dan reset ID
        $this->confirmingDeletion = false;
        $this->post_id_to_delete = null;
    }
}
