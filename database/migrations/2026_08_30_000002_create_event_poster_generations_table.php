<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_poster_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // NULL selama poster dibuat dari form tambah acara yang belum disubmit.
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assembly_id')->nullable()->constrained()->nullOnDelete();
            $table->string('style', 50);
            $table->text('prompt');
            $table->string('model', 100);
            $table->string('image_path', 255)->nullable();
            $table->string('status', 20);
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Query kuota: hitung baris sukses milik user dalam bulan berjalan.
            $table->index(['user_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_poster_generations');
    }
};
