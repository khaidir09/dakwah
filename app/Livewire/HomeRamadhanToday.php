<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\RamadhanDailyLecture;
use App\Models\RamadhanSchedule;
use Carbon\Carbon;

class HomeRamadhanToday extends Component
{
    public function render()
    {
        $today = Carbon::today();

        // 1. Get active schedules
        $schedules = RamadhanSchedule::where('is_active', true)->get();

        // 2. Build map of Schedule ID -> Today's Ramadhan Day
        $scheduleDayMap = [];
        foreach ($schedules as $schedule) {
            if (!$schedule->gregorian_start_date) continue;

            // diffInDays(date, false) returns positive if date is after, negative if before
            $diff = $schedule->gregorian_start_date->diffInDays($today, false);
            $dayIndex = (int) $diff + 1;

            // We include it if it's a valid positive day.
            // We don't strictly cap at 30 because some might have extended schedules,
            // but usually Ramadhan is 29/30. The DB query will just return empty if day doesn't exist.
            if ($dayIndex > 0) {
                 $scheduleDayMap[$schedule->id] = $dayIndex;
            }
        }

        if (empty($scheduleDayMap)) {
             return view('livewire.home-ramadhan-today', ['lectures' => collect()]);
        }

        // 3. Query Lectures
        $lectures = RamadhanDailyLecture::query()
            ->with(['schedule.assembly', 'teacher'])
            ->where(function ($query) use ($scheduleDayMap) {
                foreach ($scheduleDayMap as $scheduleId => $day) {
                    $query->orWhere(function ($q) use ($scheduleId, $day) {
                        $q->where('ramadhan_schedule_id', $scheduleId)
                          ->where('day', $day);
                    });
                }
            })
            ->get()
            ->sortBy(function ($lecture) {
                return $lecture->schedule->time;
            });

        return view('livewire.home-ramadhan-today', [
            'lectures' => $lectures,
        ]);
    }
}
