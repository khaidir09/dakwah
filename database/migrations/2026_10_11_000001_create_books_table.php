<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_arabic')->nullable();
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->foreignId('specialization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('language', 30)->nullable();
            $table->string('cover_image')->nullable();
            $table->string('publisher')->nullable();
            $table->string('publication_status', 20)->nullable();
            $table->string('external_url', 500)->nullable();
            // Naskah digital opsional; bila entri pustaka dihapus, kitab tetap ada tanpa tautan.
            $table->foreignId('library_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index('is_active');
            $table->index(['is_active', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
