<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            // Guru yang masih tercatat sebagai pengarang/pensyarah tidak boleh hilang diam-diam.
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->string('role', 20);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['book_id', 'teacher_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_teacher');
    }
};
