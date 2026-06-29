<?php

namespace Tests\Feature;

use App\Models\Library;
use App\Models\LibraryPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LibraryPurchaseTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        return $admin;
    }

    private function paidLibrary(array $overrides = []): Library
    {
        return Library::create(array_merge([
            'title' => 'Kitab Berbayar',
            'slug' => 'kitab-berbayar',
            'category' => 'Fikih',
            'description' => 'Deskripsi',
            'file_path' => 'libraries/paid/file.pdf',
            'price_type' => 'paid',
            'price' => 25000,
            'is_active' => true,
        ], $overrides));
    }

    public function test_admin_creates_paid_library_stores_file_on_private_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $admin = $this->admin();
        $file = UploadedFile::fake()->create('kitab.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('libraries.store'), [
            'title' => 'Kitab Premium',
            'category' => 'Fikih',
            'description' => 'Deskripsi',
            'price_type' => 'paid',
            'price' => 25000,
            'file' => $file,
        ]);

        $response->assertRedirect(route('libraries.index'));

        $library = Library::first();
        $this->assertSame(25000, $library->price);
        $this->assertStringStartsWith('libraries/paid/', $library->file_path);
        Storage::disk('local')->assertExists($library->file_path);
        Storage::disk('public')->assertMissing($library->file_path);
    }

    public function test_paid_library_requires_price(): void
    {
        Storage::fake('local');

        $admin = $this->admin();
        $file = UploadedFile::fake()->create('kitab.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('libraries.store'), [
            'title' => 'Kitab Tanpa Harga',
            'category' => 'Fikih',
            'description' => 'Deskripsi',
            'price_type' => 'paid',
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('price');
    }

    public function test_user_without_purchase_cannot_read_paid_library(): void
    {
        Storage::fake('local');
        $library = $this->paidLibrary();
        Storage::disk('local')->put($library->file_path, '%PDF-fake');

        $user = User::factory()->create();

        $this->actingAs($user)->get(route('pustaka-read', $library))->assertForbidden();
    }

    public function test_user_with_pending_purchase_cannot_read(): void
    {
        Storage::fake('local');
        $library = $this->paidLibrary();
        Storage::disk('local')->put($library->file_path, '%PDF-fake');

        $user = User::factory()->create();
        $library->purchases()->create([
            'user_id' => $user->id,
            'status' => LibraryPurchase::STATUS_PENDING,
            'price' => $library->price,
        ]);

        $this->actingAs($user)->get(route('pustaka-read', $library))->assertForbidden();
    }

    public function test_user_with_active_purchase_can_read(): void
    {
        Storage::fake('local');
        $library = $this->paidLibrary();
        Storage::disk('local')->put($library->file_path, '%PDF-fake');

        $user = User::factory()->create();
        $library->purchases()->create([
            'user_id' => $user->id,
            'status' => LibraryPurchase::STATUS_ACTIVE,
            'price' => $library->price,
        ]);

        $this->actingAs($user)->get(route('pustaka-read', $library))->assertOk();
    }

    public function test_admin_can_read_paid_library_without_purchase(): void
    {
        Storage::fake('local');
        $library = $this->paidLibrary();
        Storage::disk('local')->put($library->file_path, '%PDF-fake');

        $this->actingAs($this->admin())->get(route('pustaka-read', $library))->assertOk();
    }

    public function test_active_purchaser_can_stream_document_via_xhr(): void
    {
        Storage::fake('local');
        $library = $this->paidLibrary();
        Storage::disk('local')->put($library->file_path, '%PDF-fake');

        $user = User::factory()->create();
        $library->purchases()->create([
            'user_id' => $user->id,
            'status' => LibraryPurchase::STATUS_ACTIVE,
            'price' => $library->price,
        ]);

        $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('pustaka-stream', $library))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_stream_rejects_direct_non_xhr_request(): void
    {
        Storage::fake('local');
        $library = $this->paidLibrary();
        Storage::disk('local')->put($library->file_path, '%PDF-fake');

        $user = User::factory()->create();
        $library->purchases()->create([
            'user_id' => $user->id,
            'status' => LibraryPurchase::STATUS_ACTIVE,
            'price' => $library->price,
        ]);

        // Tanpa header XHR (mis. dibuka langsung di tab baru) → ditolak.
        $this->actingAs($user)->get(route('pustaka-stream', $library))->assertForbidden();
    }

    public function test_stream_forbidden_without_access(): void
    {
        Storage::fake('local');
        $library = $this->paidLibrary();
        Storage::disk('local')->put($library->file_path, '%PDF-fake');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get(route('pustaka-stream', $library))
            ->assertForbidden();
    }

    public function test_purchase_creates_pending_and_redirects_to_whatsapp(): void
    {
        config(['services.whatsapp.admin_number' => '6281234567890']);
        $library = $this->paidLibrary();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('pustaka-purchase', $library));

        $this->assertStringContainsString('wa.me/6281234567890', $response->headers->get('Location'));
        $this->assertDatabaseHas('library_purchases', [
            'user_id' => $user->id,
            'library_id' => $library->id,
            'status' => LibraryPurchase::STATUS_PENDING,
            'price' => 25000,
        ]);
    }

    public function test_purchase_does_not_duplicate_pending(): void
    {
        $library = $this->paidLibrary();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('pustaka-purchase', $library));
        $this->actingAs($user)->post(route('pustaka-purchase', $library));

        $this->assertSame(1, LibraryPurchase::where('user_id', $user->id)->where('library_id', $library->id)->count());
    }

    public function test_purchase_on_free_library_is_not_allowed(): void
    {
        $library = $this->paidLibrary(['price_type' => 'free', 'price' => null]);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('pustaka-purchase', $library))->assertNotFound();
    }

    public function test_guest_redirected_to_login_for_read_and_purchase(): void
    {
        $library = $this->paidLibrary();

        $this->get(route('pustaka-read', $library))->assertRedirect(route('login'));
        $this->post(route('pustaka-purchase', $library))->assertRedirect(route('login'));
    }

    public function test_admin_activate_sets_active_and_sends_notification(): void
    {
        config([
            'services.onesignal.app_id' => 'fake-app',
            'services.onesignal.rest_api_key' => 'fake-key',
        ]);
        Http::fake(); // fake semua request agar tidak ada panggilan jaringan nyata

        $library = $this->paidLibrary();
        $user = User::factory()->create();
        $purchase = $library->purchases()->create([
            'user_id' => $user->id,
            'status' => LibraryPurchase::STATUS_PENDING,
            'price' => $library->price,
        ]);

        $admin = $this->admin();
        $this->actingAs($admin)->put(route('admin.library-purchases.activate', $purchase))
            ->assertRedirect(route('admin.library-purchases.index'));

        $purchase->refresh();
        $this->assertSame(LibraryPurchase::STATUS_ACTIVE, $purchase->status);
        $this->assertSame($admin->id, $purchase->verified_by);
        $this->assertNotNull($purchase->verified_at);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'onesignal.com'));
    }

    public function test_admin_reject_sets_rejected(): void
    {
        $library = $this->paidLibrary();
        $user = User::factory()->create();
        $purchase = $library->purchases()->create([
            'user_id' => $user->id,
            'status' => LibraryPurchase::STATUS_PENDING,
            'price' => $library->price,
        ]);

        $this->actingAs($this->admin())->put(route('admin.library-purchases.reject', $purchase), [
            'admin_note' => 'Bukti tidak valid',
        ])->assertRedirect(route('admin.library-purchases.index'));

        $purchase->refresh();
        $this->assertSame(LibraryPurchase::STATUS_REJECTED, $purchase->status);
        $this->assertSame('Bukti tidak valid', $purchase->admin_note);
    }

    public function test_non_admin_cannot_access_admin_purchases(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.library-purchases.index'))->assertForbidden();
    }

    public function test_my_libraries_shows_only_own_purchases(): void
    {
        $library = $this->paidLibrary();
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $library->purchases()->create([
            'user_id' => $owner->id,
            'status' => LibraryPurchase::STATUS_ACTIVE,
            'price' => $library->price,
        ]);
        $library->purchases()->create([
            'user_id' => $other->id,
            'status' => LibraryPurchase::STATUS_ACTIVE,
            'price' => $library->price,
        ]);

        $response = $this->actingAs($owner)->get(route('pustaka-saya'));
        $response->assertOk();
        // Hanya satu purchase milik owner yang terlihat.
        $this->assertCount(1, $response->viewData('purchases'));
    }
}
