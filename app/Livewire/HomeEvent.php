<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class HomeEvent extends Component
{
    public function render()
    {
        $query = Event::latest();

        $query->where('date', '>=', now());
        $events = $query->take(6)->get();

        return view('livewire.home-event', [
            'events' => $events
        ]);
    }
}
