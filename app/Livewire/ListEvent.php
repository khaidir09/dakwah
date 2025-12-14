<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class ListEvent extends Component
{
    use WithPagination;

    public $paginate = 10;

    #[Url]
    public $search = '';

    #[Url]
    public $category = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Event::latest();

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('name', 'like', $searchTerm)
                    ->orWhere('location', 'like', $searchTerm);
            });
        }

        if ($this->category) {
            $query->where('category', $this->category);
        }

        $events_count = $query->count();
        $events = $query->simplePaginate($this->paginate);

        return view('livewire.list-event', [
            'events' => $events,
            'events_count' => $events_count
        ]);
    }
}
