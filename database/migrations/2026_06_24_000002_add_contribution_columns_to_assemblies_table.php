<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assemblies', function (Blueprint $table) {
            $table->enum('contribution_status', ['pending', 'approved', 'rejected'])->nullable()->after('status');
            $table->text('rejection_reason')->nullable()->after('contribution_status');
            $table->timestamp('moderated_at')->nullable()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('assemblies', function (Blueprint $table) {
            $table->dropColumn(['contribution_status', 'rejection_reason', 'moderated_at']);
        });
    }
};
