<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->foreignId('contributor_user_id')->nullable()->after('id')
                ->constrained('users')->nullOnDelete();
            $table->enum('contribution_status', ['pending', 'approved', 'rejected'])->nullable()->after('contributor_user_id');
            $table->text('rejection_reason')->nullable()->after('contribution_status');
            $table->timestamp('moderated_at')->nullable()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['contributor_user_id']);
            $table->dropColumn(['contributor_user_id', 'contribution_status', 'rejection_reason', 'moderated_at']);
        });
    }
};
