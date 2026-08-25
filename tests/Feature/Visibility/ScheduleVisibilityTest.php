<?php

namespace Tests\Feature\Visibility;

use Tests\Feature\PublicPageTestCase;

class ScheduleVisibilityTest extends PublicPageTestCase
{
    private function jadwal(array $attributes = [])
    {
        $majelis = $this->makeAssembly();
        $guru = $this->makeTeacher(['foto' => 'guru/uji.webp']);

        return $this->makeSchedule($majelis, array_merge([
            'teacher_id' => $guru->id,
        ], $attributes));
    }

    /** @test */
    public function publik_tidak_dapat_membuka_jadwal_pending(): void
    {
        $jadwal = $this->jadwal([
            'contribution_status' => 'pending',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        $this->get(route('jadwal-majelis-detail', $jadwal->route_slug))->assertNotFound();
    }

    /** @test */
    public function publik_tidak_dapat_membuka_jadwal_rejected(): void
    {
        $jadwal = $this->jadwal([
            'contribution_status' => 'rejected',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        $this->get(route('jadwal-majelis-detail', $jadwal->route_slug))->assertNotFound();
    }

    /** @test */
    public function pemilik_dapat_melihat_jadwal_pending_miliknya(): void
    {
        $pemilik = $this->kontributor();
        $jadwal = $this->jadwal([
            'contribution_status' => 'pending',
            'contributor_user_id' => $pemilik->id,
        ]);

        $this->actingAs($pemilik)
            ->get(route('jadwal-majelis-detail', $jadwal->route_slug))
            ->assertOk();
    }

    /** @test */
    public function kontributor_lain_tidak_dapat_melihat_jadwal_pending(): void
    {
        $jadwal = $this->jadwal([
            'contribution_status' => 'pending',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        $this->actingAs($this->kontributor())
            ->get(route('jadwal-majelis-detail', $jadwal->route_slug))
            ->assertNotFound();
    }

    /** @test */
    public function super_admin_dapat_melihat_jadwal_pending(): void
    {
        $jadwal = $this->jadwal([
            'contribution_status' => 'pending',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('jadwal-majelis-detail', $jadwal->route_slug))
            ->assertOk();
    }

    /** @test */
    public function jadwal_legacy_tanpa_contribution_status_tetap_publik(): void
    {
        $jadwal = $this->jadwal();

        $this->get(route('jadwal-majelis-detail', $jadwal->route_slug))->assertOk();
    }

    /** @test */
    public function jadwal_belum_disetujui_tidak_tampil_di_daftar_jadwal(): void
    {
        $majelis = $this->makeAssembly();
        $guru = $this->makeTeacher(['foto' => 'guru/uji.webp']);

        $this->makeSchedule($majelis, [
            'nama_jadwal' => 'Kajian Tayang',
            'teacher_id' => $guru->id,
        ]);
        $this->makeSchedule($majelis, [
            'nama_jadwal' => 'Kajian Menunggu Moderasi',
            'teacher_id' => $guru->id,
            'contribution_status' => 'pending',
            'contributor_user_id' => $this->kontributor()->id,
        ]);

        $this->get(route('jadwal-majelis-list'))
            ->assertOk()
            ->assertSee('Kajian Tayang')
            ->assertDontSee('Kajian Menunggu Moderasi');
    }
}
