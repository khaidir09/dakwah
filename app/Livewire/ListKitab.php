<?php

namespace App\Livewire;

use App\Models\Book;
use App\Models\Specialization;
use Livewire\Component;
use Livewire\WithPagination;

class ListKitab extends Component
{
    use WithPagination;

    public $paginate = 12;

    public $search;

    public $specialization;

    public $language;

    public $teacher_status;

    protected $updatesQueryString = ['search', 'specialization', 'language', 'teacher_status'];

    public function mount()
    {
        $this->search = request()->query('search', $this->search);
        $this->specialization = request()->query('specialization', $this->specialization);
        $this->language = request()->query('language', $this->language);
        $this->teacher_status = request()->query('teacher_status', $this->teacher_status);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSpecialization()
    {
        $this->resetPage();
    }

    public function updatingLanguage()
    {
        $this->resetPage();
    }

    public function updatingTeacherStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Book::query()
            ->active()
            ->with([
                'teachers' => fn ($q) => $q->publiclyVisible(),
                'specialization',
            ]);

        if ($this->search) {
            $searchTerm = '%'.$this->search.'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('title_arabic', 'like', $searchTerm)
                    ->orWhereHas('teachers', fn ($t) => $t->publiclyVisible()->where('name', 'like', $searchTerm));
            });
        }

        if ($this->specialization) {
            $query->where('specialization_id', $this->specialization);
        }

        if ($this->language) {
            $query->where('language', $this->language);
        }

        // Kitab ikut tersaring bila minimal satu gurunya berstatus tersebut.
        if ($this->teacher_status === 'wafat') {
            $query->whereHas('teachers', fn ($t) => $t->publiclyVisible()->wafat());
        } elseif ($this->teacher_status === 'hidup') {
            $query->whereHas('teachers', fn ($t) => $t->publiclyVisible()->masihHidup());
        }

        return view('livewire.list-kitab', [
            'books' => $query->latest()->simplePaginate($this->paginate),
            'specializations' => Specialization::whereHas('books', fn ($q) => $q->active())->orderBy('name')->pluck('name', 'id'),
            'languages' => Book::LANGUAGES,
        ]);
    }
}
