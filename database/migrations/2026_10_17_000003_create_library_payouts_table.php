<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_id')->constrained()->cascadeOnDelete();
            $table->char('period', 7); // YYYY-MM
            // Angka dibekukan saat transfer dicatat agar laporan bulan yang sudah dibayar tidak berubah.
            $table->unsignedInteger('purchase_count');
            $table->unsignedInteger('gross_amount');
            $table->unsignedTinyInteger('share_percent');
            $table->unsignedInteger('author_amount');
            $table->string('payee_name')->nullable();
            $table->date('transferred_at');
            $table->string('transfer_proof_path')->nullable();
            $table->string('admin_note', 500)->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['library_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_payouts');
    }
};
