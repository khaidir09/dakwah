<?php

namespace App\Livewire\Ramadhan;

use App\Models\RamadhanSchedule;
use App\Models\RamadhanDailyLecture;
use App\Models\Teacher;
use Livewire\Component;
use Carbon\Carbon;

class ScheduleForm extends Component
{
    public $scheduleId;
    public $hijri_year;
    public $gregorian_start_date;
    public $title;
    public $description;
    public $is_active = true;

    // Array to hold the 30 days schedule
    // Structure: ['day' => 1, 'teacher_id' => null, 'custom_speaker_name' => '', 'title' => '', 'time' => '04:30']
    public $days = [];

    public function mount($schedule = null)
    {
        if ($schedule) {
            $this->scheduleId = $schedule->id;
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
            // Defaults
            $this->hijri_year = Carbon::now()->year + 579; // Approximate
            $this->gregorian_start_date = Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d');

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
            'hijri_year' => $this->hijri_year,
            'gregorian_start_date' => $this->gregorian_start_date,
            'title' => $this->title,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->scheduleId) {
            $schedule = RamadhanSchedule::find($this->scheduleId);
            $schedule->update($data);
        } else {
            $schedule = RamadhanSchedule::create($data);
            $this->scheduleId = $schedule->id;
        }

        // Sync Lectures
        // Strategy: Delete all for this schedule and recreate.
        // Efficient enough for 30 rows and ensures clean state.
        $schedule->lectures()->delete();

        foreach ($this->days as $dayData) {
            // Only save if there is a speaker or title
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

        session()->flash('message', 'Jadwal berhasil disimpan.');
        return redirect()->route('ramadhan-schedules.index');
    }

    public function render()
    {
        return view('livewire.ramadhan.schedule-form', [
            'teachers' => Teacher::orderBy('name')->get(['id', 'name'])
        ]);
    }
}
