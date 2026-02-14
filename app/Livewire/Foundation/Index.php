<?php

namespace App\Livewire\Foundation;

use App\Models\Foundation;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $foundation = Foundation::findOrFail($id);
        $foundation->delete();

        session()->flash('message', 'Foundation successfully deleted.');
    }

    public function render()
    {
        $foundations = Foundation::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('website_url', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.foundation.index', [
            'foundations' => $foundations,
        ]);
    }
}
