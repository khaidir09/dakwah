<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\Schedule;
use App\Models\ScheduleNote;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class KelolaCatatan extends Component
{
    use WithPagination;

    public $paginate = 10;

    public $confirmingDeletion = false;
    public $note_id_to_delete;

    public $isModalOpen = false;
    public $isEditMode = false;
    public $note_id;
    public $schedule_id;
    public $content;
    public $visibility = 'Private';
    public $created_at;

    protected $rules = [
        'schedule_id' => 'required|exists:schedules,id',
        'content' => 'required|string',
        'visibility' => 'required|in:Private,Public',
        'created_at' => 'nullable|date',
    ];

    public function create()
    {
        $this->resetFields();
        $this->isEditMode = false;
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $this->resetFields();
        $note = ScheduleNote::where('user_id', Auth::id())->findOrFail($id);

        $this->note_id = $note->id;
        $this->schedule_id = $note->schedule_id;
        $this->content = $note->content;
        $this->visibility = $note->visibility;
        $this->created_at = $note->created_at ? $note->created_at->format('Y-m-d') : null;

        $this->isEditMode = true;
        $this->isModalOpen = true;
    }

    public function store()
    {
        $this->validate();

        $data = [
            'user_id' => Auth::id(),
            'schedule_id' => $this->schedule_id,
            'content' => $this->content,
            'visibility' => $this->visibility,
            'status' => $this->visibility === 'Public' ? 'Pending' : 'Approved',
        ];

        if (!empty($this->created_at)) {
            $data['created_at'] = $this->created_at . ' ' . now()->format('H:i:s');
        }

        ScheduleNote::create($data);

        session()->flash('message', 'Catatan berhasil ditambahkan.');
        $this->isModalOpen = false;
        $this->resetFields();
    }

    public function update()
    {
        $this->validate();

        $note = ScheduleNote::where('user_id', Auth::id())->findOrFail($this->note_id);

        $status = $note->status;
        if ($this->visibility === 'Public' && $note->visibility === 'Private') {
             $status = 'Pending';
        }

        $updateData = [
            'schedule_id' => $this->schedule_id,
            'content' => $this->content,
            'visibility' => $this->visibility,
            'status' => $status,
        ];

        if (!empty($this->created_at)) {
            $updateData['created_at'] = $this->created_at . ' ' . $note->created_at->format('H:i:s');
        }

        $note->update($updateData);

        session()->flash('message', 'Catatan berhasil diperbarui.');
        $this->isModalOpen = false;
        $this->resetFields();
    }

    public function confirmDelete($noteId)
    {
        $this->note_id_to_delete = $noteId;
        $this->confirmingDeletion = true;
    }

    public function deleteNote()
    {
        if ($this->note_id_to_delete) {
            $note = ScheduleNote::where('user_id', Auth::id())->find($this->note_id_to_delete);

            if ($note) {
                $note->delete();
                session()->flash('message', 'Catatan berhasil dihapus.');
            }
        }

        $this->confirmingDeletion = false;
        $this->note_id_to_delete = null;
    }

    public function resetFields()
    {
        $this->note_id = null;
        $this->schedule_id = null;
        $this->content = '';
        $this->visibility = 'Private';
        $this->created_at = null;
    }

    public function render()
    {
        $notes_count = ScheduleNote::where('user_id', Auth::id())->count();
        $query = ScheduleNote::with(['schedule.assembly', 'schedule.teacher'])
            ->where('user_id', Auth::id())
            ->latest();

        $notes = $query->simplePaginate($this->paginate);

        $availableSchedules = Schedule::whereHas('assembly', function($q) {
                $q->where('user_id', Auth::id());
            })
            ->orWhereHas('assembly.followers', function($q) {
                 $q->where('user_id', Auth::id());
            })
            ->get();

        // Provide schedules the user has notes for or follows to the form

        return view('livewire.user.kelola-catatan', [
            'notes_count' => $notes_count,
            'notes' => $notes,
            'availableSchedules' => $availableSchedules,
        ]);
    }
}
