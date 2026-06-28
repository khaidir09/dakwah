<?php

namespace Tests\Feature;

use App\Models\Assembly;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventDateValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    }

    private function contributor(): User
    {
        return User::factory()->create();
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    private function assemblyFor(User $user): Assembly
    {
        return Assembly::create([
            'user_id' => $user->id,
            'nama_majelis' => 'Majelis Test',
            'deskripsi' => 'Deskripsi',
            'alamat' => 'Jl. Test No. 1',
            'guru' => 'Guru Test',
            'maps' => 'https://maps.google.com',
            'status' => 'Aktif',
        ]);
    }

    private function eventIn(int $days): string
    {
        return Carbon::today()->addDays($days)->format('Y-m-d') . 'T08:00';
    }

    private function payload(string $date): array
    {
        return [
            'name' => 'Acara Test',
            'date' => $date,
            'access' => 'Umum',
            'category' => 'Maulid',
        ];
    }

    // --- store() tests ---

    public function test_contributor_cannot_submit_event_today(): void
    {
        $user = $this->contributor();
        $this->assemblyFor($user);

        $this->actingAs($user)
            ->post(route('kelola-acara-majelis.store'), $this->payload($this->eventIn(0)))
            ->assertRedirect()
            ->assertSessionHasErrors('date');
    }

    public function test_contributor_cannot_submit_event_less_than_7_days(): void
    {
        $user = $this->contributor();
        $this->assemblyFor($user);

        $this->actingAs($user)
            ->post(route('kelola-acara-majelis.store'), $this->payload($this->eventIn(6)))
            ->assertRedirect()
            ->assertSessionHasErrors('date');
    }

    public function test_contributor_can_submit_event_exactly_7_days_ahead(): void
    {
        $user = $this->contributor();
        $this->assemblyFor($user);

        $this->actingAs($user)
            ->post(route('kelola-acara-majelis.store'), $this->payload($this->eventIn(7)))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kelola-acara-majelis'));
    }

    public function test_contributor_can_submit_event_more_than_7_days_ahead(): void
    {
        $user = $this->contributor();
        $this->assemblyFor($user);

        $this->actingAs($user)
            ->post(route('kelola-acara-majelis.store'), $this->payload($this->eventIn(14)))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kelola-acara-majelis'));
    }

    public function test_super_admin_can_submit_event_regardless_of_date(): void
    {
        $user = $this->admin();
        $this->assemblyFor($user);

        $this->actingAs($user)
            ->post(route('kelola-acara-majelis.store'), $this->payload($this->eventIn(1)))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kelola-acara-majelis'));
    }

    // --- update() tests ---

    private function existingEvent(User $user, Assembly $assembly): Event
    {
        return Event::create([
            'assembly_id' => $assembly->id,
            'user_id' => $user->id,
            'name' => 'Acara Lama',
            'date' => Carbon::today()->addDays(30)->toDateTimeString(),
            'location' => $assembly->alamat,
            'access' => 'Umum',
            'category' => 'Maulid',
        ]);
    }

    public function test_contributor_cannot_update_event_less_than_7_days(): void
    {
        $user = $this->contributor();
        $assembly = $this->assemblyFor($user);
        $event = $this->existingEvent($user, $assembly);

        $this->actingAs($user)
            ->put(route('kelola-acara-majelis.update', $event->id), $this->payload($this->eventIn(6)))
            ->assertRedirect()
            ->assertSessionHasErrors('date');
    }

    public function test_contributor_can_update_event_exactly_7_days_ahead(): void
    {
        $user = $this->contributor();
        $assembly = $this->assemblyFor($user);
        $event = $this->existingEvent($user, $assembly);

        $this->actingAs($user)
            ->put(route('kelola-acara-majelis.update', $event->id), $this->payload($this->eventIn(7)))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kelola-acara-majelis'));
    }

    public function test_super_admin_can_update_event_regardless_of_date(): void
    {
        $user = $this->admin();
        $assembly = $this->assemblyFor($user);
        $event = $this->existingEvent($user, $assembly);

        $this->actingAs($user)
            ->put(route('kelola-acara-majelis.update', $event->id), $this->payload($this->eventIn(1)))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('kelola-acara-majelis'));
    }

    public function test_error_message_is_in_bahasa_indonesia(): void
    {
        $user = $this->contributor();
        $this->assemblyFor($user);

        $response = $this->actingAs($user)
            ->post(route('kelola-acara-majelis.store'), $this->payload($this->eventIn(3)));

        $response->assertSessionHasErrors(['date' => 'Tanggal acara harus minimal 7 hari dari hari ini.']);
    }
}
