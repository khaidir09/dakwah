<?php

namespace Tests\Feature;

use App\Models\RamadhanSchedule;
use App\Models\RamadhanDailyLecture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RamadhanScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_schedule_and_lectures()
    {
        $schedule = RamadhanSchedule::create([
            'hijri_year' => 1446,
            'gregorian_start_date' => '2025-03-01',
            'title' => 'Test Schedule',
            'is_active' => true,
        ]);

        $lecture = RamadhanDailyLecture::create([
            'ramadhan_schedule_id' => $schedule->id,
            'day' => 1,
            'title' => 'First Day',
            'time' => '04:30:00',
        ]);

        // Reload to ensure relationship can be fetched
        $lecture = $lecture->fresh(['schedule']);

        $this->assertNotNull($lecture->schedule, 'Schedule relationship is null');
        $this->assertEquals('2025-03-01', $lecture->date->format('Y-m-d'));
    }
}
