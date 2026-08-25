<?php

namespace Tests\Feature\Visibility;

use App\Livewire\HomeEvent;
use Livewire\Livewire;
use Tests\Feature\PublicPageTestCase;

class EventVisibilityTest extends PublicPageTestCase
{
    /** @test */
    public function acara_ditolak_tidak_tampil_di_halaman_acara(): void
    {
        $this->makeEvent([
            'name' => 'Acara Tayang',
            'status' => 'approved',
            'moderated_at' => now(),
        ]);
        $this->makeEvent([
            'name' => 'Acara Ditolak',
            'status' => 'rejected',
            'moderated_at' => now(),
        ]);

        $this->get(route('event-list'))
            ->assertOk()
            ->assertSee('Acara Tayang')
            ->assertDontSee('Acara Ditolak');
    }

    /** @test */
    public function acara_menunggu_moderasi_tidak_tampil_di_halaman_acara(): void
    {
        $this->makeEvent(['name' => 'Acara Menunggu Moderasi']);

        $this->get(route('event-list'))
            ->assertOk()
            ->assertDontSee('Acara Menunggu Moderasi');
    }

    /**
     * EventController::store() dan ManageEventController::store() hanya mengisi
     * `moderated_at` untuk Super Admin dan membiarkan `status` bernilai default
     * 'pending'. Memfilter dengan `status = approved` saja akan menghilangkan
     * seluruh acara buatan admin dari kanal publik.
     *
     * @test
     */
    public function acara_buatan_super_admin_tetap_tampil_di_halaman_acara(): void
    {
        $this->makeEvent([
            'name' => 'Acara Buatan Admin',
            'moderated_at' => now(),
        ]);

        $this->get(route('event-list'))
            ->assertOk()
            ->assertSee('Acara Buatan Admin');
    }

    /** @test */
    public function acara_ditolak_tidak_tampil_di_widget_beranda(): void
    {
        $this->makeEvent([
            'name' => 'Acara Tayang',
            'status' => 'approved',
            'moderated_at' => now(),
        ]);
        $this->makeEvent([
            'name' => 'Acara Ditolak',
            'status' => 'rejected',
            'moderated_at' => now(),
        ]);

        Livewire::test(HomeEvent::class)
            ->assertSee('Acara Tayang')
            ->assertDontSee('Acara Ditolak');
    }

    /** @test */
    public function acara_menunggu_moderasi_tidak_tampil_di_widget_beranda(): void
    {
        $this->makeEvent(['name' => 'Acara Menunggu Moderasi']);

        Livewire::test(HomeEvent::class)
            ->assertDontSee('Acara Menunggu Moderasi');
    }

    /** @test */
    public function acara_buatan_super_admin_tetap_tampil_di_widget_beranda(): void
    {
        $this->makeEvent([
            'name' => 'Acara Buatan Admin',
            'moderated_at' => now(),
        ]);

        Livewire::test(HomeEvent::class)
            ->assertSee('Acara Buatan Admin');
    }
}
