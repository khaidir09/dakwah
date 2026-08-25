<?php

namespace Tests\Feature\Visibility;

use Tests\Feature\PublicPageTestCase;

class AssemblyVisibilityTest extends PublicPageTestCase
{
    /** @test */
    public function publik_tidak_dapat_membuka_majelis_pending(): void
    {
        $majelis = $this->makeAssembly([
            'contribution_status' => 'pending',
            'user_id' => $this->kontributor()->id,
        ]);

        $this->get(route('majelis-detail', $majelis->route_slug))->assertNotFound();
    }

    /** @test */
    public function publik_tidak_dapat_membuka_majelis_rejected(): void
    {
        $majelis = $this->makeAssembly([
            'contribution_status' => 'rejected',
            'user_id' => $this->kontributor()->id,
        ]);

        $this->get(route('majelis-detail', $majelis->route_slug))->assertNotFound();
    }

    /** @test */
    public function pemilik_dapat_melihat_majelis_pending_miliknya(): void
    {
        $pemilik = $this->kontributor();
        $majelis = $this->makeAssembly([
            'contribution_status' => 'pending',
            'user_id' => $pemilik->id,
        ]);

        $this->actingAs($pemilik)
            ->get(route('majelis-detail', $majelis->route_slug))
            ->assertOk();
    }

    /** @test */
    public function kontributor_lain_tidak_dapat_melihat_majelis_pending(): void
    {
        $pemilik = $this->kontributor();
        $orangLain = $this->kontributor();

        $majelis = $this->makeAssembly([
            'contribution_status' => 'pending',
            'user_id' => $pemilik->id,
        ]);

        $this->actingAs($orangLain)
            ->get(route('majelis-detail', $majelis->route_slug))
            ->assertNotFound();
    }

    /** @test */
    public function super_admin_dapat_melihat_majelis_pending(): void
    {
        $majelis = $this->makeAssembly([
            'contribution_status' => 'pending',
            'user_id' => $this->kontributor()->id,
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('majelis-detail', $majelis->route_slug))
            ->assertOk();
    }

    /** @test */
    public function majelis_approved_tetap_dapat_dibuka_publik(): void
    {
        $majelis = $this->makeAssembly([
            'contribution_status' => 'approved',
            'user_id' => $this->kontributor()->id,
        ]);

        $this->get(route('majelis-detail', $majelis->route_slug))->assertOk();
    }

    /** @test */
    public function majelis_legacy_tanpa_contribution_status_tetap_publik(): void
    {
        $majelis = $this->makeAssembly();

        $this->get(route('majelis-detail', $majelis->route_slug))->assertOk();
    }

    /** @test */
    public function jadwal_belum_disetujui_tidak_tampil_di_detail_majelis(): void
    {
        $majelis = $this->makeAssembly();

        // View detail majelis menaut ke route('guru-detail', $item->teacher)
        // tanpa penjaga null, jadi setiap jadwal wajib punya guru.
        $guru = $this->makeTeacher(['foto' => 'guru/uji.webp']);

        $this->makeSchedule($majelis, [
            'nama_jadwal' => 'Kajian Tayang',
            'teacher_id' => $guru->id,
            'contribution_status' => 'approved',
        ]);
        $this->makeSchedule($majelis, [
            'nama_jadwal' => 'Kajian Menunggu Moderasi',
            'teacher_id' => $guru->id,
            'contribution_status' => 'pending',
        ]);
        $this->makeSchedule($majelis, [
            'nama_jadwal' => 'Kajian Ditolak',
            'teacher_id' => $guru->id,
            'contribution_status' => 'rejected',
        ]);

        $this->get(route('majelis-detail', $majelis->route_slug))
            ->assertOk()
            ->assertSee('Kajian Tayang')
            ->assertDontSee('Kajian Menunggu Moderasi')
            ->assertDontSee('Kajian Ditolak');
    }

    /** @test */
    public function acara_belum_disetujui_tidak_tampil_di_detail_majelis(): void
    {
        $majelis = $this->makeAssembly();

        $this->makeEvent([
            'assembly_id' => $majelis->id,
            'name' => 'Haul Tayang',
            'status' => 'approved',
            'moderated_at' => now(),
        ]);
        $this->makeEvent([
            'assembly_id' => $majelis->id,
            'name' => 'Haul Menunggu Moderasi',
            'status' => 'pending',
        ]);
        $this->makeEvent([
            'assembly_id' => $majelis->id,
            'name' => 'Haul Ditolak',
            'status' => 'rejected',
            'moderated_at' => now(),
        ]);

        $this->get(route('majelis-detail', $majelis->route_slug))
            ->assertOk()
            ->assertSee('Haul Tayang')
            ->assertDontSee('Haul Menunggu Moderasi')
            ->assertDontSee('Haul Ditolak');
    }
}
