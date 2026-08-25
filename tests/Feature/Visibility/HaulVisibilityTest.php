<?php

namespace Tests\Feature\Visibility;

use App\Livewire\HomeUpcomingHaul;
use Livewire\Livewire;
use Tests\Feature\PublicPageTestCase;

/**
 * Widget haul di beranda mengambil guru berdasarkan tanggal wafat Hijriah.
 * Tanggal Hijriah dipatok lewat Http::fake() di PublicPageTestCase:
 * "17 Syakban 1447 H" — bulan 8, hari 17.
 */
class HaulVisibilityTest extends PublicPageTestCase
{
    private function guruHaul(string $name, array $attributes = [])
    {
        return $this->makeTeacher(array_merge([
            'name' => $name,
            'wafat_hijriah_day' => 20,
            'wafat_hijriah_month' => 8,
            'wafat_hijriah_year' => 1400,
        ], $attributes));
    }

    /** @test */
    public function guru_pending_tidak_tampil_di_widget_haul(): void
    {
        $this->guruHaul('Guru Tayang');
        $this->guruHaul('Guru Menunggu Moderasi', [
            'contribution_status' => 'pending',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        Livewire::test(HomeUpcomingHaul::class)
            ->assertSee('Guru Tayang')
            ->assertDontSee('Guru Menunggu Moderasi');
    }

    /** @test */
    public function guru_rejected_tidak_tampil_di_widget_haul(): void
    {
        $this->guruHaul('Guru Ditolak', [
            'contribution_status' => 'rejected',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        Livewire::test(HomeUpcomingHaul::class)
            ->assertDontSee('Guru Ditolak');
    }

    /** @test */
    public function guru_approved_tetap_tampil_di_widget_haul(): void
    {
        $this->guruHaul('Guru Disetujui', [
            'contribution_status' => 'approved',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        Livewire::test(HomeUpcomingHaul::class)
            ->assertSee('Guru Disetujui');
    }

    /** @test */
    public function guru_pending_di_bulan_berikutnya_juga_tidak_tampil(): void
    {
        $this->guruHaul('Guru Bulan Depan Pending', [
            'wafat_hijriah_month' => 9,
            'wafat_hijriah_day' => 5,
            'contribution_status' => 'pending',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        Livewire::test(HomeUpcomingHaul::class)
            ->assertDontSee('Guru Bulan Depan Pending');
    }
}
