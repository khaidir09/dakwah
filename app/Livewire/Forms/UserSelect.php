<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Livewire\Component;

class UserSelect extends Component
{
    public $search = '';
    public $selectedUsers = [];

    // Use mount instead of direct property assignment for initial data
    public function mount($initialUsers = [])
    {
        // Convert collection to array if needed, or ensure it's in the format we want
        // We want an array of objects/arrays with id, name, email
        if ($initialUsers instanceof \Illuminate\Database\Eloquent\Collection) {
            $this->selectedUsers = $initialUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            })->toArray();
        } elseif (is_array($initialUsers)) {
            $this->selectedUsers = $initialUsers;
        }
    }

    public function updatedSearch()
    {
        // No need to do anything here, the render method handles the query
    }

    public function selectUser($id)
    {
        $user = User::find($id);
        if ($user) {
            // Check if already selected
            $exists = false;
            foreach ($this->selectedUsers as $selected) {
                if ($selected['id'] == $id) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $this->selectedUsers[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            }
        }
        $this->search = ''; // Clear search after selection
    }

    public function removeUser($index)
    {
        unset($this->selectedUsers[$index]);
        $this->selectedUsers = array_values($this->selectedUsers); // Re-index array
    }

    public function render()
    {
        $searchResults = [];

        if (strlen($this->search) >= 2) {
            $searchResults = User::query()
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->limit(5)
                ->get();
        }

        return view('livewire.forms.user-select', [
            'searchResults' => $searchResults,
        ]);
    }
}
