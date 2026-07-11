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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('cover_image');
            $table->string('attachment_filename')->nullable()->after('attachment_path');
            $table->string('attachment_label')->nullable()->after('attachment_filename');
            $table->unsignedInteger('downloads_count')->default(0)->after('attachment_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'attachment_path',
                'attachment_filename',
                'attachment_label',
                'downloads_count',
            ]);
        });
    }
};
