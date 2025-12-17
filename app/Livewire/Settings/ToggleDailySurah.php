<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ToggleDailySurah extends Component
{
    public $enabled;

    public function mount()
    {
        $this->enabled = Auth::user()->is_daily_surah_enabled;
    }

    public function updatedEnabled($value)
    {
        $user = Auth::user();
        $user->is_daily_surah_enabled = $value;
        $user->save();

        $this->dispatch('saved'); // Optional: notify user
    }

    public function render()
    {
        return view('livewire.settings.toggle-daily-surah');
    }
}
