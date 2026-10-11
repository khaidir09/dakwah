<?php

namespace App\Livewire\Partials;

use App\Models\Book;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Daftar baris (guru + peran) di form kitab. Tiap baris memakai TeacherSelect
 * sebagai child component; `key` stabil per baris supaya pilihan guru di baris
 * lain tidak ikut ter-reset saat baris ditambah atau dihapus.
 */
class BookTeacherRows extends Component
{
    public array $rows = [];

    public function mount(array $rows = [])
    {
        $this->rows = array_map(fn (array $row) => [
            'key' => (string) Str::uuid(),
            'teacher_id' => $row['teacher_id'] ?? null,
            'role' => $row['role'] ?? 'pengarang',
        ], $rows ?: [['teacher_id' => null, 'role' => 'pengarang']]);
    }

    public function addRow()
    {
        $this->rows[] = ['key' => (string) Str::uuid(), 'teacher_id' => null, 'role' => 'pengarang'];
    }

    public function removeRow($index)
    {
        if (count($this->rows) <= 1) {
            return;
        }

        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    public function render()
    {
        return view('livewire.partials.book-teacher-rows', [
            'roles' => Book::ROLES,
        ]);
    }
}
