<?php

namespace Tests\Feature;

use App\Models\Foundation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagedFoundationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_benchmark_store_article_citations()
    {
        $user = User::factory()->create();
        $foundation = Foundation::create([
            'name' => 'Test Foundation',
            'logo_path' => 'path/to/logo.png', // required field
            'website_url' => 'https://example.com' // required field
        ]);

        \DB::table('foundation_user')->insert([
            'user_id' => $user->id,
            'foundation_id' => $foundation->id,
        ]);

        $citations = [];
        for ($i = 0; $i < 100; $i++) {
            $citations[] = [
                'type' => 'QURAN',
                'source_text_arabic' => 'بسم الله',
                'translation' => 'In the name of Allah',
                'reference' => 'Al-Fatihah: 1',
            ];
        }

        $data = [
            'title' => 'Test Benchmark Article',
            'foundation_id' => $foundation->id,
            'author_name' => 'Author Name',
            'category' => 'Sains & Syariat',
            'content' => 'Test Content',
            'citations' => $citations,
        ];

        // Benchmark
        $startTime = microtime(true);
        \DB::enableQueryLog();

        $response = $this->actingAs($user)->post(route('kelola-artikel.store'), $data);

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        $queries = count(\DB::getQueryLog());

        if ($response->exception) {
            echo "Exception: " . $response->exception->getMessage() . "\n";
            echo "Validation Errors: " . json_encode(session('errors')?->all()) . "\n";
        }

        $response->assertSessionHas('message', 'Artikel berhasil ditambahkan!');

        echo "\n[BASELINE] Store Execution Time: " . round($executionTime, 2) . " ms\n";
        echo "[BASELINE] Store Query Count: " . $queries . "\n";
    }
}
