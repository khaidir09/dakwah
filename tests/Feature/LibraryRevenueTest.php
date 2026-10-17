<?php

namespace Tests\Feature;

use App\Models\Library;
use App\Models\LibraryPayout;
use App\Models\LibraryPurchase;
use App\Models\User;
use App\Services\LibraryRevenueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LibraryRevenueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
        $this->travelTo('2026-11-10 09:00:00');
    }

    private function admin(): User
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($role);

        return $admin;
    }

    private function library(array $overrides = []): Library
    {
        return Library::create(array_merge([
            'title' => 'Buku Ustadz',
            'slug' => 'buku-ustadz',
            'category' => 'Fikih',
            'description' => 'Deskripsi',
            'file_path' => 'libraries/paid/full.pdf',
            'price_type' => 'paid',
            'price' => 50000,
            'payee_name' => 'Ustadz Fulan',
            'author_share_percent' => 70,
            'is_active' => true,
        ], $overrides));
    }

    private function purchase(Library $library, string $status, ?string $verifiedAt, int $price = 50000, ?User $user = null): LibraryPurchase
    {
        return $library->purchases()->create([
            'user_id' => ($user ?? User::factory()->create())->id,
            'status' => $status,
            'price' => $price,
            'verified_at' => $verifiedAt,
        ]);
    }

    private function markPaidPayload(array $overrides = []): array
    {
        return array_merge([
            'bulan' => '2026-10',
            'transferred_at' => '2026-11-05',
            'transfer_proof' => UploadedFile::fake()->image('bukti.jpg'),
        ], $overrides);
    }

    public function test_hanya_pembelian_aktif_pada_bulan_konfirmasi_yang_dihitung(): void
    {
        $library = $this->library();
        $this->purchase($library, LibraryPurchase::STATUS_ACTIVE, '2026-10-01 00:00:00');
        $this->purchase($library, LibraryPurchase::STATUS_ACTIVE, '2026-10-31 23:59:59');
        $this->purchase($library, LibraryPurchase::STATUS_ACTIVE, '2026-09-30 23:59:59');
        $this->purchase($library, LibraryPurchase::STATUS_ACTIVE, '2026-11-01 00:00:00');
        $this->purchase($library, LibraryPurchase::STATUS_PENDING, null);
        $this->purchase($library, LibraryPurchase::STATUS_REJECTED, '2026-10-15 10:00:00');

        $row = app(LibraryRevenueService::class)->monthlySummary('2026-10')->sole();

        $this->assertSame(2, $row['purchase_count']);
        $this->assertSame(100000, $row['gross_amount']);
        $this->assertSame(70000, $row['author_amount']);
        $this->assertSame(30000, $row['platform_amount']);
        $this->assertNull($row['payout']);
    }

    public function test_pembulatan_bagian_penulis_ke_bawah_dan_total_selalu_utuh(): void
    {
        $library = $this->library();
        $this->purchase($library, LibraryPurchase::STATUS_ACTIVE, '2026-10-10 10:00:00', 33333);

        $row = app(LibraryRevenueService::class)->summaryFor($library, '2026-10');

        $this->assertSame(23333, $row['author_amount']);
        $this->assertSame(10000, $row['platform_amount']);
        $this->assertSame($row['gross_amount'], $row['author_amount'] + $row['platform_amount']);
    }

    public function test_pustaka_tanpa_bagi_hasil_tidak_masuk_laporan(): void
    {
        $this->library();
        $tanpa = $this->library(['slug' => 'tanpa', 'title' => 'Tanpa Bagi Hasil', 'payee_name' => null, 'author_share_percent' => null]);
        $this->purchase($tanpa, LibraryPurchase::STATUS_ACTIVE, '2026-10-10 10:00:00');

        $rows = app(LibraryRevenueService::class)->monthlySummary('2026-10');

        $this->assertSame(['Buku Ustadz'], $rows->map(fn ($row) => $row['library']->title)->all());
    }

    public function test_admin_menyimpan_dan_mengosongkan_bagi_hasil_di_form_pustaka(): void
    {
        $admin = $this->admin();
        $library = $this->library(['payee_name' => null, 'author_share_percent' => null]);
        $payload = [
            'title' => $library->title,
            'category' => 'Fikih',
            'description' => 'Deskripsi',
            'price_type' => 'paid',
            'price' => 50000,
            'is_active' => '1',
        ];

        $this->actingAs($admin)->put(route('libraries.update', $library), $payload + ['payee_name' => 'Ustadz Fulan'])
            ->assertSessionHasErrors('author_share_percent');

        $this->actingAs($admin)->put(route('libraries.update', $library), $payload + ['payee_name' => 'Ustadz Fulan', 'author_share_percent' => 70])
            ->assertSessionHasNoErrors();
        $library->refresh();
        $this->assertSame('Ustadz Fulan', $library->payee_name);
        $this->assertSame(70, $library->author_share_percent);

        $this->actingAs($admin)->put(route('libraries.update', $library), array_merge($payload, ['price_type' => 'free', 'price' => null]))
            ->assertSessionHasNoErrors();
        $library->refresh();
        $this->assertNull($library->payee_name);
        $this->assertNull($library->author_share_percent);
    }

    public function test_catat_transfer_membekukan_angka_dan_tidak_bisa_ganda(): void
    {
        $admin = $this->admin();
        $library = $this->library();
        $this->purchase($library, LibraryPurchase::STATUS_ACTIVE, '2026-10-10 10:00:00');

        $this->actingAs($admin)->post(route('admin.library-revenue.mark-paid', $library), $this->markPaidPayload())
            ->assertRedirect(route('admin.library-revenue.show', [$library, 'bulan' => '2026-10']))
            ->assertSessionHasNoErrors();

        $payout = LibraryPayout::sole();
        $this->assertSame(35000, $payout->author_amount);
        $this->assertSame(70, $payout->share_percent);
        $this->assertSame('Ustadz Fulan', $payout->payee_name);
        $this->assertSame($admin->id, $payout->processed_by);
        Storage::disk('local')->assertExists($payout->transfer_proof_path);

        $library->update(['author_share_percent' => 50, 'payee_name' => 'Nama Baru']);
        $row = app(LibraryRevenueService::class)->summaryFor($library->refresh(), '2026-10');
        $this->assertSame(35000, $row['author_amount']);
        $this->assertSame(70, $row['share_percent']);
        $this->assertSame('Ustadz Fulan', $row['payee_name']);

        $this->actingAs($admin)->post(route('admin.library-revenue.mark-paid', $library), $this->markPaidPayload())
            ->assertSessionHasErrors('bulan');
        $this->assertSame(1, LibraryPayout::count());
    }

    public function test_transfer_tidak_bisa_dicatat_untuk_bulan_berjalan_atau_tanpa_bagi_hasil(): void
    {
        $admin = $this->admin();
        $library = $this->library();

        $this->actingAs($admin)->post(route('admin.library-revenue.mark-paid', $library), $this->markPaidPayload(['bulan' => '2026-11']))
            ->assertSessionHasErrors('bulan');

        $tanpa = $this->library(['slug' => 'tanpa', 'payee_name' => null, 'author_share_percent' => null]);
        $this->actingAs($admin)->post(route('admin.library-revenue.mark-paid', $tanpa), $this->markPaidPayload())
            ->assertSessionHasErrors('bulan');

        $this->assertSame(0, LibraryPayout::count());
    }

    public function test_perubahan_setelah_transfer_ditandai(): void
    {
        $library = $this->library();
        $purchase = $this->purchase($library, LibraryPurchase::STATUS_ACTIVE, '2026-10-10 10:00:00');

        $this->actingAs($this->admin())->post(route('admin.library-revenue.mark-paid', $library), $this->markPaidPayload());

        $purchase->update(['status' => LibraryPurchase::STATUS_REJECTED]);

        $row = app(LibraryRevenueService::class)->summaryFor($library, '2026-10');
        $this->assertTrue($row['changed_after_payout']);
        $this->assertSame(35000, $row['author_amount']);
    }

    public function test_laporan_cetak_tanpa_identitas_pembeli(): void
    {
        $library = $this->library();
        $buyer = User::factory()->create(['name' => 'Pembeli Rahasia', 'email' => 'rahasia@example.com']);
        $this->purchase($library, LibraryPurchase::STATUS_ACTIVE, '2026-10-10 10:00:00', 50000, $buyer);

        $this->actingAs($this->admin())
            ->get(route('admin.library-revenue.report', [$library, 'bulan' => '2026-10']))
            ->assertOk()
            ->assertSee('Oktober 2026')
            ->assertSee('Ustadz Fulan')
            ->assertSee('Rp 35.000')
            ->assertSee('Belum ditransfer')
            ->assertDontSee('Pembeli Rahasia')
            ->assertDontSee('rahasia@example.com');
    }

    public function test_halaman_ringkasan_dan_rincian_admin(): void
    {
        $admin = $this->admin();
        $library = $this->library();
        $this->purchase($library, LibraryPurchase::STATUS_ACTIVE, '2026-10-10 10:00:00');

        $this->actingAs($admin)->get(route('admin.library-revenue.index'))
            ->assertOk()
            ->assertSee('Oktober 2026')
            ->assertSee('Buku Ustadz')
            ->assertSee('Belum ditransfer');

        $this->actingAs($admin)->get(route('admin.library-revenue.show', [$library, 'bulan' => '2026-10']))
            ->assertOk()
            ->assertSee('Catat Transfer Rp 35.000');
    }

    public function test_non_admin_ditolak(): void
    {
        $library = $this->library();
        $this->purchase($library, LibraryPurchase::STATUS_ACTIVE, '2026-10-10 10:00:00');
        $this->actingAs($this->admin())->post(route('admin.library-revenue.mark-paid', $library), $this->markPaidPayload());
        $payout = LibraryPayout::sole();

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.library-revenue.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.library-revenue.show', $library))->assertForbidden();
        $this->actingAs($user)->get(route('admin.library-revenue.report', $library))->assertForbidden();
        $this->actingAs($user)->get(route('admin.library-revenue.proof', $payout))->assertForbidden();
        $this->actingAs($user)->post(route('admin.library-revenue.mark-paid', $library), $this->markPaidPayload(['bulan' => '2026-09']))
            ->assertForbidden();

        $this->actingAs($this->admin())->get(route('admin.library-revenue.proof', $payout))->assertOk();
    }
}
