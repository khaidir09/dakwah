<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Services\HijriConverter;
use App\Services\OneSignalService;
use Carbon\CarbonImmutable;
use Mockery\MockInterface;

class HaulRemindTest extends PublicPageTestCase
{
    private CarbonImmutable $today;

    protected function setUp(): void
    {
        parent::setUp();

        $this->today = CarbonImmutable::parse('2026-10-19', 'Asia/Makassar');
        $this->travelTo($this->today->setTime(6, 0));
    }

    /**
     * Guru dengan tanggal wafat Hijriah yang jatuh pada hari ke-$days dari hari ini.
     */
    private function guruHaul(string $name, int $days, array $attributes = []): Teacher
    {
        $hijri = (new HijriConverter)->toHijri($this->today->addDays($days));

        return $this->makeTeacher(array_merge([
            'name' => $name,
            'wafat_hijriah_day' => $hijri['day'],
            'wafat_hijriah_month' => $hijri['month'],
            'wafat_hijriah_year' => 1400,
        ], $attributes));
    }

    /** @test */
    public function one_push_lists_hauls_for_today_and_tomorrow(): void
    {
        $this->guruHaul('KH. Hari Ini', 0);
        $this->guruHaul('KH. Besok', 1);
        $this->guruHaul('KH. Besok Juga', 1);
        $this->guruHaul('KH. Pekan Depan', 7);

        $this->mock(OneSignalService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendToAll')
                ->once()
                ->with(
                    'Haul Guru',
                    'Hari ini: KH. Hari Ini. Besok: KH. Besok, KH. Besok Juga. Mari hadiahkan Al-Fatihah untuk beliau.',
                    url('/')
                )
                ->andReturn(['id' => 'x']);
        });

        $this->artisan('haul:remind')->assertSuccessful();
    }

    /** @test */
    public function a_single_haul_links_to_the_teacher_page(): void
    {
        $guru = $this->guruHaul('KH. Tunggal', 1);

        $this->mock(OneSignalService::class, function (MockInterface $mock) use ($guru) {
            $mock->shouldReceive('sendToAll')
                ->once()
                ->withArgs(fn ($title, $message, $url) => $message === 'Besok: KH. Tunggal. Mari hadiahkan Al-Fatihah untuk beliau.'
                    && $url === url('/guru/'.$guru->slug))
                ->andReturn(['id' => 'x']);
        });

        $this->artisan('haul:remind')->assertSuccessful();
    }

    /** @test */
    public function nothing_is_sent_without_a_haul_today_or_tomorrow(): void
    {
        $this->guruHaul('KH. Lusa', 2);
        $this->guruHaul('KH. Menunggu Moderasi', 0, [
            'contribution_status' => 'pending',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        $this->mock(OneSignalService::class, fn (MockInterface $mock) => $mock->shouldNotReceive('sendToAll'));

        $this->artisan('haul:remind')->assertSuccessful();
    }

    /** @test */
    public function it_is_sent_at_most_once_per_day(): void
    {
        $this->guruHaul('KH. Hari Ini', 0);

        $this->mock(OneSignalService::class, fn (MockInterface $mock) => $mock->shouldReceive('sendToAll')->once()->andReturn(['id' => 'x']));

        $this->artisan('haul:remind')->assertSuccessful();
        $this->artisan('haul:remind')->assertSuccessful();
    }

    /** @test */
    public function a_failed_push_can_be_retried_the_same_day(): void
    {
        $this->guruHaul('KH. Hari Ini', 0);

        $this->mock(OneSignalService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendToAll')->once()->andReturn(false);
            $mock->shouldReceive('sendToAll')->once()->andReturn(['id' => 'x']);
        });

        $this->artisan('haul:remind')->assertFailed();
        $this->artisan('haul:remind')->assertSuccessful();
    }
}
