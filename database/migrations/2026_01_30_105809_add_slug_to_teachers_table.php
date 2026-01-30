<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Backfill logic
        $teachers = DB::table('teachers')->get();
        foreach ($teachers as $teacher) {
            $slug = Str::slug($teacher->name);
            $originalSlug = $slug;
            $count = 1;

            while (DB::table('teachers')->where('slug', $slug)->where('id', '!=', $teacher->id)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            DB::table('teachers')->where('id', $teacher->id)->update(['slug' => $slug]);
        }

        Schema::table('teachers', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
