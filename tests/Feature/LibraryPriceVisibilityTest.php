<?php

namespace Tests\Feature;

use App\Models\Library;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LibraryPriceVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
    }

    private function library(array $overrides = []): Library
    {
        Storage::disk('local')->put('libraries/preview/bab1.pdf', 'ISI-BAB-I');

        return Library::create(array_merge([
            'title' => 'Buku Ustadz',
            'slug' => 'buku-ustadz',
            'category' => 'Fikih',
            'description' => 'Deskripsi',
            'file_path' => 'libraries/paid/full.pdf',
            'preview_file_path' => 'libraries/preview/bab1.pdf',
            'price_type' => 'paid',
            'price' => 75000,
            'is_active' => true,
        ], $overrides));
    }

    public function test_pustaka_lama_default_menampilkan_harga(): void
    {
        $library = $this->library();

        $this->assertTrue($library->refresh()->show_price);

        $this->actingAs(User::factory()->create())
            ->get(route('pustaka-detail', $library))
            ->assertOk()
            ->assertSee('Beli (Rp 75.000)');
    }

    public function test_harga_tersembunyi_tidak_tampil_di_detail_dan_pratinjau(): void
    {
        $library = $this->library(['show_price' => false]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('pustaka-detail', $library))
            ->assertOk()
            ->assertSee('Harga diinformasikan via WhatsApp.')
            ->assertDontSee('75.000');

        $this->actingAs($user)
            ->get(route('pustaka-preview', $library))
            ->assertOk()
            ->assertSee('pratinjau gratis', false)
            ->assertDontSee('75.000');
    }

    public function test_pesan_whatsapp_tetap_menyebut_harga_saat_disembunyikan(): void
    {
        config(['services.whatsapp.admin_number' => '6281234567890']);
        $library = $this->library(['show_price' => false]);

        $response = $this->actingAs(User::factory()->create())
            ->post(route('pustaka-purchase', $library));

        $location = $response->headers->get('Location');
        $this->assertStringStartsWith('https://wa.me/6281234567890', $location);
        $this->assertStringContainsString(rawurlencode('Harga: Rp75.000'), $location);
    }

    public function test_tanpa_nomor_whatsapp_harga_disebut_di_pesan_konfirmasi(): void
    {
        config(['services.whatsapp.admin_number' => null]);
        $library = $this->library(['show_price' => false]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('pustaka-purchase', $library))
            ->assertRedirect(route('pustaka-detail', $library));

        $this->actingAs($user)
            ->get(route('pustaka-detail', $library))
            ->assertSee('Permintaan pembelian tercatat dengan harga Rp75.000');
    }

    public function test_admin_mengatur_tampilan_harga(): void
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($role);
        $library = $this->library();
        $payload = [
            'title' => $library->title,
            'category' => 'Fikih',
            'description' => 'Deskripsi',
            'price_type' => 'paid',
            'price' => 75000,
            'is_active' => '1',
        ];

        $this->actingAs($admin)->put(route('libraries.update', $library), $payload)->assertSessionHasNoErrors();
        $this->assertFalse($library->refresh()->show_price);

        $this->actingAs($admin)->put(route('libraries.update', $library), $payload + ['show_price' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue($library->refresh()->show_price);
    }
}
