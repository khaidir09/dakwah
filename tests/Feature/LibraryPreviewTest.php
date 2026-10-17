<?php

namespace Tests\Feature;

use App\Models\Library;
use App\Models\LibraryPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LibraryPreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
    }

    private function admin(): User
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        return $admin;
    }

    private function paidLibrary(array $overrides = []): Library
    {
        Storage::disk('local')->put('libraries/paid/full.pdf', 'ISI-LENGKAP');
        Storage::disk('local')->put('libraries/preview/bab1.pdf', 'ISI-BAB-I');

        return Library::create(array_merge([
            'title' => 'Buku Ustadz',
            'slug' => 'buku-ustadz',
            'category' => 'Fikih',
            'description' => 'Deskripsi',
            'file_path' => 'libraries/paid/full.pdf',
            'preview_file_path' => 'libraries/preview/bab1.pdf',
            'price_type' => 'paid',
            'price' => 50000,
            'is_active' => true,
        ], $overrides));
    }

    private function buyer(Library $library): User
    {
        $user = User::factory()->create();
        $library->purchases()->create([
            'user_id' => $user->id,
            'status' => LibraryPurchase::STATUS_ACTIVE,
            'price' => $library->price,
        ]);

        return $user;
    }

    private function adminPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Buku Ustadz',
            'category' => 'Fikih',
            'description' => 'Deskripsi',
            'price_type' => 'paid',
            'price' => 50000,
            'is_active' => '1',
        ], $overrides);
    }

    public function test_tamu_bisa_membaca_pratinjau_dan_hanya_menerima_file_pratinjau(): void
    {
        $library = $this->paidLibrary();

        $this->get(route('pustaka-preview', $library))
            ->assertOk()
            ->assertSee('pratinjau gratis', false)
            ->assertSee(route('pustaka-preview-stream', $library), false)
            ->assertDontSee(route('pustaka-stream', $library), false)
            ->assertSee('Login untuk Membeli');

        $response = $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('pustaka-preview-stream', $library))
            ->assertOk();

        $this->assertSame('ISI-BAB-I', $response->streamedContent());
    }

    public function test_stream_pratinjau_tanpa_xhr_ditolak(): void
    {
        $library = $this->paidLibrary();

        $this->get(route('pustaka-preview-stream', $library))->assertForbidden();
    }

    public function test_pratinjau_tidak_tersedia_untuk_pustaka_tanpa_pratinjau_gratis_atau_nonaktif(): void
    {
        $tanpaPratinjau = $this->paidLibrary(['slug' => 'tanpa-pratinjau', 'preview_file_path' => null]);
        $gratis = $this->paidLibrary(['slug' => 'gratis', 'price_type' => 'free', 'price' => null]);
        $nonaktif = $this->paidLibrary(['slug' => 'nonaktif', 'is_active' => false]);

        foreach ([$tanpaPratinjau, $gratis, $nonaktif] as $library) {
            $this->get(route('pustaka-preview', $library))->assertNotFound();
            $this->withHeader('X-Requested-With', 'XMLHttpRequest')
                ->get(route('pustaka-preview-stream', $library))
                ->assertNotFound();
        }
    }

    public function test_versi_lengkap_tetap_tertutup_bagi_yang_belum_membeli(): void
    {
        $library = $this->paidLibrary();

        $this->get(route('pustaka-read', $library))->assertRedirect(route('login'));
        $this->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('pustaka-stream', $library))
            ->assertRedirect(route('login'));

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('pustaka-read', $library))->assertForbidden();
        $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('pustaka-stream', $library))
            ->assertForbidden();
    }

    public function test_pembeli_yang_membuka_pratinjau_diarahkan_ke_versi_lengkap(): void
    {
        $library = $this->paidLibrary();

        $this->actingAs($this->buyer($library))
            ->get(route('pustaka-preview', $library))
            ->assertRedirect(route('pustaka-read', $library));
    }

    public function test_pratinjau_menampilkan_status_menunggu_verifikasi(): void
    {
        $library = $this->paidLibrary();
        $user = User::factory()->create();
        $library->purchases()->create([
            'user_id' => $user->id,
            'status' => LibraryPurchase::STATUS_PENDING,
            'price' => $library->price,
        ]);

        $this->actingAs($user)
            ->get(route('pustaka-preview', $library))
            ->assertOk()
            ->assertSee('Menunggu verifikasi pembayaran');
    }

    public function test_admin_bisa_melihat_pratinjau_pustaka_nonaktif(): void
    {
        $library = $this->paidLibrary(['is_active' => false]);

        $this->actingAs($this->admin())->get(route('pustaka-preview', $library))->assertOk();
    }

    public function test_halaman_detail_menampilkan_tombol_pratinjau_hanya_untuk_yang_belum_punya_akses(): void
    {
        $library = $this->paidLibrary();

        $this->get(route('pustaka-detail', $library))
            ->assertOk()
            ->assertSee('Baca Pratinjau Gratis')
            ->assertSee(route('pustaka-preview', $library), false);

        $this->actingAs($this->buyer($library))
            ->get(route('pustaka-detail', $library))
            ->assertOk()
            ->assertDontSee('Baca Pratinjau Gratis');
    }

    public function test_admin_mengunggah_pratinjau_ke_disk_privat(): void
    {
        $this->actingAs($this->admin())->post(route('libraries.store'), $this->adminPayload([
            'file' => UploadedFile::fake()->create('lengkap.pdf', 500, 'application/pdf'),
            'preview_file' => UploadedFile::fake()->create('bab1.pdf', 100, 'application/pdf'),
        ]))->assertRedirect(route('libraries.index'));

        $library = Library::firstOrFail();
        $this->assertStringStartsWith('libraries/preview/', $library->preview_file_path);
        Storage::disk('local')->assertExists($library->preview_file_path);
        Storage::disk('public')->assertMissing($library->preview_file_path);
        $this->assertTrue($library->hasPreview());
    }

    public function test_pratinjau_diabaikan_untuk_pustaka_gratis(): void
    {
        $this->actingAs($this->admin())->post(route('libraries.store'), $this->adminPayload([
            'price_type' => 'free',
            'price' => null,
            'file' => UploadedFile::fake()->create('lengkap.pdf', 500, 'application/pdf'),
            'preview_file' => UploadedFile::fake()->create('bab1.pdf', 100, 'application/pdf'),
        ]))->assertRedirect(route('libraries.index'));

        $this->assertNull(Library::firstOrFail()->preview_file_path);
        $this->assertEmpty(Storage::disk('local')->allFiles('libraries/preview'));
    }

    public function test_admin_mengganti_pratinjau_menghapus_file_lama(): void
    {
        $library = $this->paidLibrary();

        $this->actingAs($this->admin())->put(route('libraries.update', $library), $this->adminPayload([
            'preview_file' => UploadedFile::fake()->create('bab1-baru.pdf', 100, 'application/pdf'),
        ]))->assertRedirect(route('libraries.index'));

        $library->refresh();
        Storage::disk('local')->assertMissing('libraries/preview/bab1.pdf');
        Storage::disk('local')->assertExists($library->preview_file_path);
        Storage::disk('local')->assertExists('libraries/paid/full.pdf');
    }

    public function test_admin_menghapus_pratinjau_lewat_checkbox(): void
    {
        $library = $this->paidLibrary();

        $this->actingAs($this->admin())->put(route('libraries.update', $library), $this->adminPayload([
            'remove_preview' => '1',
        ]))->assertRedirect(route('libraries.index'));

        $this->assertNull($library->refresh()->preview_file_path);
        Storage::disk('local')->assertMissing('libraries/preview/bab1.pdf');
    }

    public function test_update_tanpa_perubahan_pratinjau_mempertahankan_file(): void
    {
        $library = $this->paidLibrary();

        $this->actingAs($this->admin())->put(route('libraries.update', $library), $this->adminPayload())
            ->assertRedirect(route('libraries.index'));

        $this->assertSame('libraries/preview/bab1.pdf', $library->refresh()->preview_file_path);
        Storage::disk('local')->assertExists('libraries/preview/bab1.pdf');
    }

    public function test_ubah_ke_gratis_dan_hapus_pustaka_ikut_menghapus_pratinjau(): void
    {
        $library = $this->paidLibrary();

        $this->actingAs($this->admin())->put(route('libraries.update', $library), $this->adminPayload([
            'price_type' => 'free',
            'price' => null,
        ]))->assertRedirect(route('libraries.index'));

        $this->assertNull($library->refresh()->preview_file_path);
        Storage::disk('local')->assertMissing('libraries/preview/bab1.pdf');

        $lain = $this->paidLibrary(['slug' => 'buku-lain', 'title' => 'Buku Lain']);
        $this->actingAs($this->admin())->delete(route('libraries.destroy', $lain))
            ->assertRedirect(route('libraries.index'));

        Storage::disk('local')->assertMissing('libraries/preview/bab1.pdf');
    }
}
