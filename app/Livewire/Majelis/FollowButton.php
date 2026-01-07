<?php

namespace App\Livewire\Majelis;

use Livewire\Component;
use App\Models\Assembly;
use Illuminate\Support\Facades\Auth;

class FollowButton extends Component
{
    public Assembly $assembly;
    public bool $isFollowing = false;

    public function mount(Assembly $assembly)
    {
        $this->assembly = $assembly;
        if (Auth::check()) {
            $this->isFollowing = Auth::user()->followingAssemblies()->where('assembly_id', $assembly->id)->exists();
        }
    }

    public function toggleFollow()
    {
        if (!Auth::check()) {
            // toast
            session()->flash('message', 'Silakan login terlebih dahulu untuk mengikuti majelis.');
            return redirect()->route('login');
        }

        /** @var \App\Models\User */
        $user = Auth::user();

        if ($this->isFollowing) {
            $user->followingAssemblies()->detach($this->assembly->id);
            $this->isFollowing = false;
        } else {
            $user->followingAssemblies()->attach($this->assembly->id);
            $this->isFollowing = true;
        }
    }

    public function render()
    {
        return view('livewire.majelis.follow-button');
    }
}
