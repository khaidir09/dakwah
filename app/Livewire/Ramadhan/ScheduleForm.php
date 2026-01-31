<?php

namespace App\Livewire\Ramadhan;

use App\Models\RamadhanSchedule;
use App\Models\RamadhanDailyLecture;
use App\Models\Teacher;
use App\Models\Assembly;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ScheduleForm extends Component
{
    public $scheduleId;
    public $assembly_id;
    public $hijri_year;
    public $gregorian_start_date;
    public $title;
    public $description;
    public $is_active = true;
    public $isAdmin = false;

    // Array to hold the 30 days schedule
    public $days = [];

    public function mount($schedule = null, $assembly_id = null)
    {
        // Determine if user is admin (Super Admin)
        // Adjust this logic based on your actual permission system
        $user = Auth::user();
        $this->isAdmin = $user && ($user->hasRole('Super Admin') || $user->email === 'admin@example.com'); // Fallback check or verify Role existence

        if ($schedule) {
            $this->scheduleId = $schedule->id;
            $this->assembly_id = $schedule->assembly_id;
            $this->hijri_year = $schedule->hijri_year;
            $this->gregorian_start_date = $schedule->gregorian_start_date->format('Y-m-d');
            $this->title = $schedule->title;
            $this->description = $schedule->description;
            $this->is_active = $schedule->is_active;

            // Load existing lectures
            $lectures = $schedule->lectures()->orderBy('day')->get()->keyBy('day');
            
            for ($i = 1; $i <= 30; $i++) {
                if (isset($lectures[$i])) {
                    $l = $lectures[$i];
                    $this->days[$i] = [
                        'day' => $i,
                        'teacher_id' => $l->teacher_id,
                        'custom_speaker_name' => $l->custom_speaker_name,
                        'title' => $l->title,
                        'time' => Carbon::parse($l->time)->format('H:i'),
                    ];
                } else {
                    $this->days[$i] = $this->emptyDay($i);
                }
            }
        } else {
            // New Schedule
            $this->hijri_year = Carbon::now()->year + 579;
            $this->gregorian_start_date = Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d');
            
            // Set assembly_id
            if ($assembly_id) {
                $this->assembly_id = $assembly_id;
            } elseif (!$this->isAdmin) {
                // If not admin, try to find user's assembly
                $userAssembly = Assembly::where('user_id', $user->id)->first();
                if ($userAssembly) {
                    $this->assembly_id = $userAssembly->id;
                }
            }
            
            for ($i = 1; $i <= 30; $i++) {
                $this->days[$i] = $this->emptyDay($i);
            }
        }
    }

    private function emptyDay($dayIndex)
    {
        return [
            'day' => $dayIndex,
            'teacher_id' => null,
            'custom_speaker_name' => '',
            'title' => '',
            'time' => '04:30',
        ];
    }

    protected function rules()
    {
        return [
            'assembly_id' => 'required|exists:assemblies,id',
            'hijri_year' => 'required|integer',
            'gregorian_start_date' => 'required|date',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'days.*.teacher_id' => 'nullable|exists:teachers,id',
            'days.*.custom_speaker_name' => 'nullable|string',
            'days.*.title' => 'nullable|string',
            'days.*.time' => 'required',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'assembly_id' => $this->assembly_id,
            'hijri_year' => $this->hijri_year,
            'gregorian_start_date' => $this->gregorian_start_date,
            'title' => $this->title,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->scheduleId) {
            $schedule = RamadhanSchedule::find($this->scheduleId);
            // Verify ownership if not admin
            if (!$this->isAdmin && $schedule->assembly->user_id !== Auth::id()) {
                abort(403);
            }
            $schedule->update($data);
        } else {
            // Verify ownership of assembly if not admin
            if (!$this->isAdmin) {
                $assembly = Assembly::find($this->assembly_id);
                if ($assembly->user_id !== Auth::id()) {
                    abort(403);
                }
            }
            $schedule = RamadhanSchedule::create($data);
            $this->scheduleId = $schedule->id;
        }

        // Sync Lectures
        $schedule->lectures()->delete();

        foreach ($this->days as $dayData) {
            if (!empty($dayData['teacher_id']) || !empty($dayData['custom_speaker_name']) || !empty($dayData['title'])) {
                $schedule->lectures()->create([
                    'day' => $dayData['day'],
                    'teacher_id' => $dayData['teacher_id'] ?: null,
                    'custom_speaker_name' => $dayData['custom_speaker_name'],
                    'title' => $dayData['title'],
                    'time' => $dayData['time'],
                ]);
            }
        }

        session()->flash('message', 'Jadwal Ramadhan berhasil disimpan.');
        
        if ($this->isAdmin) {
            return redirect()->route('ramadhan-schedules.index');
        } else {
            return redirect()->route('kelola-ramadhan.index');
        }
    }

    public function render()
    {
        $assemblies = [];
        if ($this->isAdmin) {
            $assemblies = Assembly::orderBy('nama_majelis')->get();
        }

        return view('livewire.ramadhan.schedule-form', [
            'teachers' => Teacher::orderBy('name')->get(['id', 'name']),
            'assemblies' => $assemblies,
        ]);
    }
}
