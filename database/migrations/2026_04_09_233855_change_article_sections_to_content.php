<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scientific_articles', function (Blueprint $table) {
            $table->longText('content')->nullable()->after('status');
        });


        $articles = DB::table('scientific_articles')->get();
        foreach ($articles as $article) {
            $sections = DB::table('article_sections')
                ->where('article_id', $article->id)
                ->orderBy('order')
                ->get();

            $content = '';
            foreach ($sections as $section) {
                $content .= '<h2>' . $section->heading . '</h2>';
                $content .= '<p>' . nl2br($section->content) . '</p>';
            }

            if ($content !== '') {
                DB::table('scientific_articles')
                    ->where('id', $article->id)
                    ->update(['content' => $content]);
            }
        }

        Schema::dropIfExists('article_sections');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('article_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('scientific_articles')->onDelete('cascade');
            $table->string('heading');
            $table->longText('content');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::table('scientific_articles', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }
};
