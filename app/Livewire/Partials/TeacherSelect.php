<?php

namespace App\Livewire\Partials;

use App\Models\Teacher;
use Livewire\Component;

class TeacherSelect extends Component
{
    public $search = '';
    public $selectedTeacherId = null;
    public $selectedTeacherName = '';
    public $fieldName = 'teacher_id';

    public function mount($selectedId = null, $fieldName = 'teacher_id')
    {
        $this->fieldName = $fieldName;

        if ($selectedId) {
            $this->selectedTeacherId = $selectedId;
            $teacher = Teacher::find($selectedId);
            if ($teacher) {
                $this->selectedTeacherName = $teacher->name;
                $this->search = $teacher->name;
            }
        }
    }

    public function updatedSearch()
    {
        if ($this->selectedTeacherName !== $this->search) {
             $this->selectedTeacherId = null;
        }
    }

    public function selectTeacher($id, $name)
    {
        $this->selectedTeacherId = $id;
        $this->selectedTeacherName = $name;
        $this->search = $name;
    }

    public function render()
    {
        $teachers = [];

        // Only search if we don't have a valid selection that matches the search text
        // or if the search text is effectively new input
        if (strlen($this->search) >= 2 && !$this->selectedTeacherId) {
             $teachers = Teacher::where('name', 'like', '%' . $this->search . '%')
                ->where('wafat_masehi', null)
                ->take(10)
                ->get();
        }

        return view('livewire.partials.teacher-select', [
            'teachers' => $teachers
        ]);
    }
}
