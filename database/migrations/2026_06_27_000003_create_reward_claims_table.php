<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount');       // snapshot reward_settings.amount saat klaim
            $table->unsignedInteger('xp_at_claim');  // snapshot total_khidmah_points saat klaim
            $table->string('ewallet_type');          // Dana | GoPay | OVO | ShopeePay
            $table->string('ewallet_number');
            $table->string('ewallet_holder_name');
            $table->enum('status', ['pending', 'paid', 'rejected'])->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->text('admin_note')->nullable();          // keterangan admin saat paid
            $table->string('transfer_proof_path')->nullable(); // bukti transfer (disk privat 'local', webp)
            $table->timestamp('transferred_at')->nullable();   // tanggal transfer (diisi admin)
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_claims');
    }
};
