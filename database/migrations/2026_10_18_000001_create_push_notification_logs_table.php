<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 100);
            $table->string('message', 300);
            $table->string('url')->nullable();
            $table->string('target', 10); // all | users
            $table->json('user_ids')->nullable();
            $table->string('status', 10); // sent | failed
            $table->string('onesignal_id')->nullable();
            $table->unsignedInteger('recipients')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_notification_logs');
    }
};
