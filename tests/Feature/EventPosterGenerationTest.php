<?php

namespace Tests\Feature;

use App\Models\Assembly;
use App\Models\Event;
use App\Models\EventPosterGeneration;
use App\Models\PosterSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventPosterGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.gemini.api_key' => 'kunci-uji',
            'services.gemini.image_model' => 'gemini-2.5-flash-image',
            'services.gemini.timeout' => 5,
        ]);

        Storage::fake('public');
    }

    /** Respons Gemini yang memuat satu gambar PNG 2x2 sebagai base64. */
    private function fakeSuccess(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'inlineData' => [
                                'mimeType' => 'image/png',
                                'data' => base64_encode($this->pngBytes()),
                            ],
                        ]],
                    ],
                ]],
            ]),
        ]);
    }

    private function pngBytes(): string
    {
        // Dibuat lewat Intervention agar dijamin dapat didekode kembali oleh service.
        return (string) Image::create(8, 8)->toPng();
    }

    private function pemilikMajelis(): User
    {
        $user = User::factory()->create();

        Assembly::create([
            'user_id' => $user->id,
            'nama_majelis' => 'Majelis Nurul Iman',
            'deskripsi' => 'Deskripsi majelis uji.',
            'alamat' => 'Jl. Uji No. 1, Banjarmasin',
            'guru' => 'Guru Uji',
            'maps' => 'https://maps.google.com',
            'status' => 'Aktif',
        ]);

        return $user;
    }

    private function kontributor(): User
    {
        $role = Role::firstOrCreate(['name' => 'Kontributor', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Maulid Akbar',
            'category' => 'Maulid',
            'date' => Carbon::today()->addDays(14)->setTime(19, 30)->format('Y-m-d\TH:i'),
            'style' => 'kaligrafi_emas',
        ], $overrides);
    }

    public function test_pemilik_majelis_dapat_generate_poster(): void
    {
        $this->fakeSuccess();
        $user = $this->pemilikMajelis();

        $response = $this->actingAs($user)->postJson(route('poster-acara.generate'), $this->payload());

        $response->assertOk()
            ->assertJsonStructure(['generation_id', 'preview_url', 'quota']);

        $generation = EventPosterGeneration::first();
        $this->assertSame(EventPosterGeneration::STATUS_SUCCESS, $generation->status);
        $this->assertStringStartsWith('events/large/', $generation->image_path);
        $this->assertNull($generation->event_id);

        Storage::disk('public')->assertExists($generation->image_path);
        Storage::disk('public')->assertExists(str_replace('/large/', '/thumb/', $generation->image_path));

        $this->assertSame(4, $response->json('quota.remaining'));
    }

    public function test_kontributor_dapat_generate_poster(): void
    {
        $this->fakeSuccess();

        $this->actingAs($this->kontributor())
            ->postJson(route('poster-acara.generate'), $this->payload())
            ->assertOk();
    }

    public function test_user_tanpa_majelis_dan_bukan_kontributor_ditolak(): void
    {
        Http::fake();

        $this->actingAs(User::factory()->create())
            ->postJson(route('poster-acara.generate'), $this->payload())
            ->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_generate_ditolak_saat_kuota_habis(): void
    {
        Http::fake();
        $user = $this->pemilikMajelis();

        $kuota = PosterSetting::current()->monthly_quota;

        for ($i = 0; $i < $kuota; $i++) {
            EventPosterGeneration::create([
                'user_id' => $user->id,
                'style' => 'kaligrafi_emas',
                'prompt' => 'prompt uji',
                'model' => 'gemini-2.5-flash-image',
                'image_path' => 'events/large/lama-'.$i.'.webp',
                'status' => EventPosterGeneration::STATUS_SUCCESS,
            ]);
        }

        $this->actingAs($user)
            ->postJson(route('poster-acara.generate'), $this->payload())
            ->assertStatus(422);

        Http::assertNothingSent();
    }

    public function test_generate_ditolak_saat_fitur_dinonaktifkan(): void
    {
        Http::fake();
        PosterSetting::current()->update(['is_active' => false]);

        $this->actingAs($this->pemilikMajelis())
            ->postJson(route('poster-acara.generate'), $this->payload())
            ->assertStatus(422);

        Http::assertNothingSent();
    }

    public function test_generate_gagal_tidak_mengurangi_kuota(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response('kesalahan server', 500),
        ]);

        $user = $this->pemilikMajelis();

        $this->actingAs($user)
            ->postJson(route('poster-acara.generate'), $this->payload())
            ->assertStatus(502);

        $generation = EventPosterGeneration::first();
        $this->assertSame(EventPosterGeneration::STATUS_FAILED, $generation->status);
        $this->assertNull($generation->image_path);

        $this->assertSame(0, EventPosterGeneration::quotaUsedThisMonth($user->id));
    }

    public function test_poster_terlampir_ke_acara_saat_submit(): void
    {
        $this->fakeSuccess();
        $user = $this->pemilikMajelis();

        $generationId = $this->actingAs($user)
            ->postJson(route('poster-acara.generate'), $this->payload())
            ->json('generation_id');

        $generation = EventPosterGeneration::find($generationId);

        $this->actingAs($user)->post(route('kelola-acara-majelis.store'), [
            'name' => 'Maulid Akbar',
            'category' => 'Maulid',
            'date' => Carbon::today()->addDays(14)->setTime(19, 30)->format('Y-m-d\TH:i'),
            'access' => 'Umum',
            'generation_id' => $generationId,
        ])->assertRedirect(route('kelola-acara-majelis'));

        $event = Event::first();
        $this->assertSame($generation->image_path, $event->image);
        $this->assertSame($event->id, $generation->fresh()->event_id);
    }

    public function test_generation_id_milik_user_lain_diabaikan(): void
    {
        $this->fakeSuccess();

        $orang_lain = $this->pemilikMajelis();
        $generationId = $this->actingAs($orang_lain)
            ->postJson(route('poster-acara.generate'), $this->payload())
            ->json('generation_id');

        $penyerang = $this->pemilikMajelis();

        $this->actingAs($penyerang)->post(route('kelola-acara-majelis.store'), [
            'name' => 'Acara Curian',
            'category' => 'Taklim',
            'date' => Carbon::today()->addDays(14)->setTime(19, 30)->format('Y-m-d\TH:i'),
            'access' => 'Umum',
            'generation_id' => $generationId,
        ])->assertRedirect(route('kelola-acara-majelis'));

        $event = Event::where('name', 'Acara Curian')->first();
        $this->assertNull($event->image);
        $this->assertNull(EventPosterGeneration::find($generationId)->event_id);
    }

    public function test_form_tambah_acara_menampilkan_blok_poster_ai(): void
    {
        $this->actingAs($this->pemilikMajelis())
            ->get(route('kelola-acara-majelis.create'))
            ->assertOk()
            ->assertSee('Buat Poster dengan AI', false)
            ->assertSee('Kaligrafi Emas');
    }

    public function test_form_tambah_acara_menyembunyikan_blok_untuk_user_tidak_berhak(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('kelola-acara-majelis.create'))
            ->assertOk()
            ->assertDontSee('Kaligrafi Emas');
    }

    public function test_accessor_thumb_mengembalikan_path_flat_untuk_acara_lama(): void
    {
        $lama = new Event(['image' => 'events/abc.webp']);
        $this->assertStringContainsString('events/abc.webp', $lama->image_thumb_url);
        $this->assertStringNotContainsString('/thumb/', $lama->image_thumb_url);

        $baru = new Event(['image' => 'events/large/abc.webp']);
        $this->assertStringContainsString('events/thumb/abc.webp', $baru->image_thumb_url);
        $this->assertStringContainsString('events/large/abc.webp', $baru->image_large_url);
    }
}
