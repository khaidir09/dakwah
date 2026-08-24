<?php

namespace Tests\Feature\Api;

use App\Models\Wirid;

class WiridApiTest extends ApiTestCase
{
    private function makeWirid(array $attributes = []): Wirid
    {
        return Wirid::create(array_merge([
            'kategori' => 'wirid',
            'nama' => 'Istighfar',
            'arab' => '<p>أَسْتَغْفِرُ اللهَ</p>',
            'arti' => '<p>Aku memohon ampun kepada Allah</p>',
            'deskripsi' => '<p>Dibaca setiap pagi</p>',
            'jumlah' => 100,
            'waktu' => 'Pagi',
        ], $attributes));
    }

    /** @test */
    public function it_returns_wirids_with_the_expected_structure()
    {
        $this->makeWirid();

        $this->apiGet('/api/v1/wirid')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'id', 'kategori', 'nama', 'waktu', 'jumlah',
                    'arab_html', 'arab_text', 'arti_html', 'arti_text',
                    'deskripsi_html', 'deskripsi_text', 'url',
                ]],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ])
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function it_provides_both_html_and_plain_text_variants()
    {
        $this->makeWirid(['arti' => '<p>Baris satu</p><p>Baris dua</p>']);

        $response = $this->apiGet('/api/v1/wirid')->assertOk();

        $this->assertStringContainsString('<p>', $response->json('data.0.arab_html'));
        $this->assertStringNotContainsString('<', $response->json('data.0.arab_text'));
        $this->assertSame('Baris satuBaris dua', $response->json('data.0.arti_text'));
    }

    /** @test */
    public function it_strips_dangerous_html_from_stored_content()
    {
        $this->makeWirid(['deskripsi' => '<p>aman</p><script>alert(1)</script>']);

        $response = $this->apiGet('/api/v1/wirid')->assertOk();

        $this->assertStringNotContainsString('<script', $response->json('data.0.deskripsi_html'));
        $this->assertStringNotContainsString('<script', $response->json('data.0.deskripsi_text'));
    }

    /** @test */
    public function null_fields_stay_null()
    {
        $this->makeWirid(['arti' => null, 'deskripsi' => null]);

        $response = $this->apiGet('/api/v1/wirid')->assertOk();

        $this->assertNull($response->json('data.0.arti_html'));
        $this->assertNull($response->json('data.0.arti_text'));
        $this->assertNull($response->json('data.0.deskripsi_html'));
    }

    /** @test */
    public function it_filters_by_kategori_and_waktu()
    {
        $this->makeWirid(['kategori' => 'wirid', 'waktu' => 'Pagi']);
        $this->makeWirid(['kategori' => 'doa', 'nama' => 'Doa Safar', 'waktu' => 'Petang']);

        $this->apiGet('/api/v1/wirid?kategori=doa')->assertOk()->assertJsonCount(1, 'data');
        $this->apiGet('/api/v1/wirid?waktu=Pagi')->assertOk()->assertJsonCount(1, 'data');
        $this->apiGet('/api/v1/wirid')->assertOk()->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_rejects_an_invalid_kategori_or_per_page()
    {
        $this->apiGet('/api/v1/wirid?kategori=lainnya')->assertStatus(422);
        $this->apiGet('/api/v1/wirid?per_page=999')->assertStatus(422);
    }

    /** @test */
    public function it_paginates_and_returns_an_empty_page_beyond_the_last()
    {
        for ($i = 0; $i < 3; $i++) {
            $this->makeWirid(['nama' => 'Wirid '.$i]);
        }

        $this->apiGet('/api/v1/wirid?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 2);

        $this->apiGet('/api/v1/wirid?per_page=2&page=99')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 3);
    }
}
