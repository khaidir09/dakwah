<?php

namespace Tests\Feature\User;

use App\Models\Foundation;
use App\Models\ScientificArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ScientificArticleTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup shared data
    }

    public function test_store_article_with_relations()
    {
        $user = User::factory()->create();
        $foundation = Foundation::create([
            'name' => 'Test Foundation',
            'website_url' => 'https://example.com',
            'logo_path' => 'logo.jpg'
        ]);

        $user->foundations()->attach($foundation->id);
        $this->actingAs($user);

        $data = [
            'title' => 'Test Article',
            'subtitle' => 'Subtitle',
            'foundation_id' => $foundation->id,
            'author_name' => 'Author Name',
            'category' => 'Fiqih',
            'status' => 'DRAFT',
            'sections' => [
                [
                    'heading' => 'Section 1',
                    'content' => 'Content 1',
                    'order' => 1
                ],
                [
                    'heading' => 'Section 2',
                    'content' => 'Content 2',
                    'order' => 2
                ]
            ],
            'citations' => [
                [
                    'type' => 'QURAN',
                    'source_text_arabic' => 'بسم الله',
                    'translation' => 'In the name of Allah',
                    'reference' => 'Al-Fatihah: 1'
                ]
            ],
            'bibliography' => [
                [
                    'full_citation' => 'Author. (2023). Title. Publisher.'
                ]
            ]
        ];

        $response = $this->post(route('kelola-artikel.store'), $data);

        $response->assertRedirect(route('kelola-artikel.index'));

        $this->assertDatabaseHas('scientific_articles', ['title' => 'Test Article']);
        $article = ScientificArticle::where('title', 'Test Article')->first();

        $this->assertDatabaseHas('article_sections', [
            'article_id' => $article->id,
            'heading' => 'Section 1'
        ]);
        $this->assertDatabaseHas('article_citations', [
            'article_id' => $article->id,
            'type' => 'QURAN'
        ]);
        $this->assertDatabaseHas('article_bibliography', [
            'article_id' => $article->id,
            'full_citation' => 'Author. (2023). Title. Publisher.'
        ]);
    }

    public function test_update_article_with_relations()
    {
        $user = User::factory()->create();
        $foundation = Foundation::create([
            'name' => 'Test Foundation',
            'website_url' => 'https://example.com',
            'logo_path' => 'logo.jpg'
        ]);

        $user->foundations()->attach($foundation->id);
        $this->actingAs($user);

        $article = ScientificArticle::create([
            'foundation_id' => $foundation->id,
            'title' => 'Original Title',
            'slug' => 'original-title',
            'author_name' => 'Original Author',
            'category' => 'Fiqih',
            'status' => 'DRAFT'
        ]);

        $article->sections()->create(['heading' => 'Old Section', 'content' => 'Old Content', 'order' => 1]);

        $data = [
            'title' => 'Updated Title',
            'foundation_id' => $foundation->id,
            'author_name' => 'Updated Author',
            'category' => 'Fiqih',
            'status' => 'PUBLISHED',
            'sections' => [
                [
                    'heading' => 'New Section',
                    'content' => 'New Content',
                    'order' => 1
                ]
            ],
            // Sending empty citations/bibliography to verify update handles them (or lack thereof)
        ];

        $response = $this->put(route('kelola-artikel.update', $article->id), $data);

        $response->assertRedirect(route('kelola-artikel.index'));

        $this->assertDatabaseHas('scientific_articles', ['title' => 'Updated Title']);
        $this->assertDatabaseMissing('article_sections', ['heading' => 'Old Section']);
        $this->assertDatabaseHas('article_sections', ['heading' => 'New Section']);
    }
}
