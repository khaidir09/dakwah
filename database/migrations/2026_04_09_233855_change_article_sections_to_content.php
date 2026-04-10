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
        Schema::table('scientific_articles', function (Blueprint $table) {
            $table->longText('content')->nullable()->after('status');
        });

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
