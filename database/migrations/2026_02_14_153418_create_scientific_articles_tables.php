<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel foundations (Mitra Yayasan)
        Schema::create('foundations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_path');
            $table->string('website_url');
            $table->timestamps();
        });

        // 2. Tabel scientific_articles (Header Artikel)
        Schema::create('scientific_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foundation_id')->constrained('foundations')->onDelete('cascade');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('slug')->unique();
            $table->string('author_name');
            $table->string('category');
            $table->dateTime('published_at')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('notebook_id')->nullable();
            $table->enum('status', ['DRAFT', 'PUBLISHED'])->default('DRAFT');
            // views count
            $table->unsignedBigInteger('views_count')->default(0);
            // likes count
            $table->unsignedBigInteger('likes_count')->default(0);
            // shares count
            $table->unsignedBigInteger('shares_count')->default(0);
            // estimated_reading_time
            $table->integer('estimated_reading_time')->default(0);
            $table->timestamps();
        });

        // 3. Tabel article_sections (Konten Tersegmentasi)
        Schema::create('article_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('scientific_articles')->onDelete('cascade');
            $table->string('heading');
            $table->longText('content');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 4. Tabel article_citations (Khazanah Dalil & Referensi)
        Schema::create('article_citations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('scientific_articles')->onDelete('cascade');
            $table->enum('type', ['QURAN', 'HADITH', 'KITAB', 'SAINS']);
            $table->text('source_text_arabic')->nullable();
            $table->text('translation')->nullable();
            $table->string('reference');
            $table->timestamps();
        });

        // 5. Tabel article_bibliography (Daftar Pustaka)
        Schema::create('article_bibliography', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('scientific_articles')->onDelete('cascade');
            $table->text('full_citation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_bibliography');
        Schema::dropIfExists('article_citations');
        Schema::dropIfExists('article_sections');
        Schema::dropIfExists('scientific_articles');
        Schema::dropIfExists('foundations');
    }
};
