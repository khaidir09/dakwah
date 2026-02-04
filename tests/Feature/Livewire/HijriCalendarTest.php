<?php

namespace Tests\Feature\Livewire;

use App\Livewire\HijriCalendar;
use Livewire\Livewire;
use Tests\TestCase;

class HijriCalendarTest extends TestCase
{
    /** @test */
    public function the_component_can_render()
    {
        Livewire::test(HijriCalendar::class)
            ->assertStatus(200);
    }
}
