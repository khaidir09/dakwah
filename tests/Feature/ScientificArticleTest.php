<?php

namespace Tests\Feature;

use App\Models\ArticleBibliography;
use App\Models\ArticleCitation;
use App\Models\ArticleSection;
use App\Models\Foundation;
use App\Models\Library;
use App\Models\ScientificArticle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ScientificArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_scientific_article_structure()
    {
        // 1. Create Foundation
        $foundation = Foundation::create([
            'name' => 'Yayasan Test',
            'logo_path' => '/path/to/logo.png',
            'website_url' => 'https://example.com',
        ]);

        $this->assertDatabaseHas('foundations', ['name' => 'Yayasan Test']);

        // 2. Create Scientific Article
        $article = ScientificArticle::create([
            'foundation_id' => $foundation->id,
            'title' => 'Test Article',
            'subtitle' => 'Subtitle',
            'slug' => 'test-article',
            'author_name' => 'Author Name',
            'category' => 'Sains & Syariat',
            'published_at' => now(),
            'status' => 'DRAFT',
        ]);

        $this->assertDatabaseHas('scientific_articles', ['slug' => 'test-article']);
        $this->assertEquals($foundation->id, $article->foundation->id);

        // 3. Create Section
        $section = ArticleSection::create([
            'article_id' => $article->id,
            'heading' => 'Section 1',
            'content' => 'Content here',
            'order' => 1,
        ]);

        $this->assertDatabaseHas('article_sections', ['heading' => 'Section 1']);
        $this->assertEquals($article->id, $section->scientificArticle->id);

        // 4. Create Citation
        $citation = ArticleCitation::create([
            'article_id' => $article->id,
            'type' => 'QURAN',
            'source_text_arabic' => 'بسم الله',
            'reference' => 'Al-Fatihah: 1',
        ]);

        $this->assertDatabaseHas('article_citations', ['type' => 'QURAN']);
        $this->assertEquals($article->id, $citation->scientificArticle->id);

        // 5. Create Bibliography
        // Create Library first
        $library = Library::create([
            'title' => 'Kitab Test',
            'slug' => 'kitab-test',
            'category' => 'Fikih',
            'description' => 'Desc',
            'file_path' => '/path/book.pdf',
        ]);

        $bibliography = ArticleBibliography::create([
            'article_id' => $article->id,
            'full_citation' => 'Citation Text',
            'kitab_id' => $library->id,
        ]);

        $this->assertDatabaseHas('article_bibliography', ['full_citation' => 'Citation Text']);
        $this->assertEquals($article->id, $bibliography->scientificArticle->id);
        $this->assertEquals($library->id, $bibliography->library->id);
    }
}
