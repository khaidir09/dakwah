<?php

namespace Tests\Feature;

use App\Models\Assembly;
use App\Models\User;
use App\Services\OneSignalService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;

/**
 * 2026-10-19 jatuh pada hari Senin; jadwal default makeSchedule() adalah
 * Senin 19:30, sehingga pengingatnya jatuh pada run 17:30.
 */
class JadwalRemindTest extends PublicPageTestCase
{
    private function at(string $time): void
    {
        $this->travelTo(CarbonImmutable::parse($time, 'Asia/Makassar'));
    }

    private function followedAssembly(int $followers = 1, array $attributes = []): Assembly
    {
        $assembly = $this->makeAssembly($attributes);
        $assembly->followers()->attach(User::factory()->count($followers)->create());

        return $assembly;
    }

    private function expectNoPush(): void
    {
        $this->mock(OneSignalService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('sendNotification'));
    }

    /** @test */
    public function followers_receive_a_push_two_hours_before_the_schedule(): void
    {
        $assembly = $this->followedAssembly(2, ['nama_majelis' => 'Majelis Ar-Raudhah']);
        $schedule = $this->makeSchedule($assembly, ['teacher_id' => $this->makeTeacher(['name' => 'KH. Uji'])->id]);
        $this->followedAssembly(1, ['nama_majelis' => 'Majelis Lain']);

        $expectedIds = $assembly->followers()->pluck('users.id')->all();

        $this->mock(OneSignalService::class, function (MockInterface $mock) use ($expectedIds, $schedule) {
            $mock->shouldReceive('sendNotification')
                ->once()
                ->withArgs(function ($title, $message, $userIds, $url) use ($expectedIds, $schedule) {
                    return $title === 'Majelis Ar-Raudhah'
                        && $message === 'Kajian Uji hari ini pukul 19:30 WITA bersama KH. Uji.'
                        && $userIds == $expectedIds
                        && $url === route('jadwal-majelis-detail', $schedule->route_slug);
                })
                ->andReturn(['id' => 'x']);
        });

        $this->at('2026-10-19 17:30:20');

        $this->artisan('jadwal:remind')->assertSuccessful();
    }

    /** @test */
    public function the_window_is_rounded_down_to_fifteen_minutes(): void
    {
        $this->makeSchedule($this->followedAssembly());
        $this->expectNoPush();

        // 17:20 dibulatkan ke 17:15 -> jendela 19:15–19:30, jadwal 19:30 belum masuk.
        $this->at('2026-10-19 17:20:00');

        $this->artisan('jadwal:remind')->assertSuccessful();
    }

    /** @test */
    public function the_same_occurrence_is_only_reminded_once(): void
    {
        $this->makeSchedule($this->followedAssembly());

        $this->mock(OneSignalService::class, fn (MockInterface $mock) => $mock->shouldReceive('sendNotification')->once()->andReturn(['id' => 'x']));

        $this->at('2026-10-19 17:30:00');
        $this->artisan('jadwal:remind')->assertSuccessful();

        $this->at('2026-10-19 17:44:00');
        $this->artisan('jadwal:remind')->assertSuccessful();
    }

    /** @test */
    public function schedules_on_another_day_are_skipped(): void
    {
        $this->makeSchedule($this->followedAssembly(), ['hari' => 'Selasa']);
        $this->expectNoPush();

        $this->at('2026-10-19 17:30:00');

        $this->artisan('jadwal:remind')->assertSuccessful();
    }

    /** @test */
    public function assemblies_without_followers_are_skipped(): void
    {
        $this->makeSchedule($this->makeAssembly());
        $this->expectNoPush();

        $this->at('2026-10-19 17:30:00');

        $this->artisan('jadwal:remind')->assertSuccessful();
    }

    /** @test */
    public function inactive_and_unmoderated_schedules_are_skipped(): void
    {
        $assembly = $this->followedAssembly();
        $this->makeSchedule($assembly, ['status' => 'Libur Ramadhan']);
        $this->makeSchedule($assembly, [
            'contribution_status' => 'pending',
            'contributor_user_id' => $this->kontributor()->id,
        ]);
        $this->makeSchedule($this->followedAssembly(1, ['contribution_status' => 'pending']));
        $this->expectNoPush();

        $this->at('2026-10-19 17:30:00');

        $this->artisan('jadwal:remind')->assertSuccessful();
    }

    /** @test */
    public function early_morning_schedules_are_reminded_the_night_before(): void
    {
        $this->makeSchedule($this->followedAssembly(), ['hari' => 'Selasa', 'waktu' => '2026-01-01 05:00:00']);

        $this->mock(OneSignalService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendNotification')
                ->once()
                ->withArgs(fn ($title, $message) => $message === 'Kajian Uji besok pukul 05:00 WITA.')
                ->andReturn(['id' => 'x']);
        });

        $this->at('2026-10-19 21:00:00');
        $this->artisan('jadwal:remind --subuh')->assertSuccessful();

        // Run reguler 03:00 tidak boleh mengirim pengingat dini hari.
        $this->at('2026-10-20 03:00:00');
        $this->artisan('jadwal:remind')->assertSuccessful();
    }

    /** @test */
    public function the_subuh_run_ignores_schedules_from_seven_onwards(): void
    {
        $this->makeSchedule($this->followedAssembly(), ['hari' => 'Selasa', 'waktu' => '2026-01-01 07:00:00']);
        $this->expectNoPush();

        $this->at('2026-10-19 21:00:00');

        $this->artisan('jadwal:remind --subuh')->assertSuccessful();
    }

    /** @test */
    public function followers_are_sent_in_batches_of_two_thousand(): void
    {
        $assembly = $this->makeAssembly();
        $this->makeSchedule($assembly);

        $password = Hash::make('password');
        foreach (array_chunk(range(1, 2001), 500) as $chunk) {
            DB::table('users')->insert(array_map(fn ($i) => [
                'name' => 'Jamaah '.$i,
                'email' => 'jamaah'.$i.'@example.test',
                'password' => $password,
            ], $chunk));
        }
        $assembly->followers()->attach(User::pluck('id'));

        $this->mock(OneSignalService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendNotification')
                ->once()->withArgs(fn ($t, $m, $userIds) => count($userIds) === 2000)->andReturn(['id' => 'a']);
            $mock->shouldReceive('sendNotification')
                ->once()->withArgs(fn ($t, $m, $userIds) => count($userIds) === 1)->andReturn(['id' => 'b']);
        });

        $this->at('2026-10-19 17:30:00');

        $this->artisan('jadwal:remind')->assertSuccessful();
    }

    /** @test */
    public function a_failed_push_makes_the_command_fail(): void
    {
        $this->makeSchedule($this->followedAssembly());

        $this->mock(OneSignalService::class, fn (MockInterface $mock) => $mock->shouldReceive('sendNotification')->once()->andReturn(false));

        $this->at('2026-10-19 17:30:00');

        $this->artisan('jadwal:remind')->assertFailed();
    }
}
