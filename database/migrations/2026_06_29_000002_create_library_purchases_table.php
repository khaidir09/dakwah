<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('library_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'active', 'rejected'])->default('pending');
            // Snapshot harga saat permintaan dibuat agar akses pembeli tak terpengaruh perubahan harga.
            $table->unsignedInteger('price');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'library_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_purchases');
    }
};
