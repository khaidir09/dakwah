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
        // Add maps column to teachers
        Schema::table('teachers', function (Blueprint $table) {
            $table->text('maps')->nullable()->after('biografi');
        });

        // Migrate data
        $biographies = DB::table('biographies')->get();

        foreach ($biographies as $bio) {
            // Check if teacher with same slug exists
            $slug = $bio->slug;
            $originalSlug = $slug;
            $count = 1;

            while (DB::table('teachers')->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-merged-' . $count++;
            }

            // Prepare data for teachers
            $teacherData = [
                'name' => $bio->nama,
                'slug' => $slug,
                'biografi' => $bio->deskripsi,
                'foto' => $bio->foto,
                'maps' => $bio->maps,
                'wafat_masehi' => $bio->tanggal_wafat_masehi,
                'wafat_hijriah' => $bio->tanggal_wafat_hijriah,
                'source' => $bio->source, // Assuming source exists in both
                'domisili' => '-', // Required in teachers
                'created_at' => $bio->created_at,
                'updated_at' => $bio->updated_at,
            ];

            DB::table('teachers')->insert($teacherData);
        }

        // Drop biographies table
        Schema::dropIfExists('biographies');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create biographies table
        Schema::create('biographies', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->string('foto')->nullable();
            $table->longText('deskripsi');
            $table->text('maps')->nullable();
            $table->date('tanggal_wafat_masehi')->nullable();
            $table->string('tanggal_wafat_hijriah')->nullable();
            $table->json('source')->nullable();
            $table->timestamps();
        });

        // Move data back (best effort) from teachers that were originally biographies
        // This is tricky because we don't track origin.
        // We will assume teachers with 'maps' not null are biographies (heuristic).

        $teachers = DB::table('teachers')->whereNotNull('maps')->get();

        foreach ($teachers as $teacher) {
            DB::table('biographies')->insert([
                'nama' => $teacher->name,
                'slug' => $teacher->slug,
                'foto' => $teacher->foto,
                'deskripsi' => $teacher->biografi,
                'maps' => $teacher->maps,
                'tanggal_wafat_masehi' => $teacher->wafat_masehi,
                'tanggal_wafat_hijriah' => $teacher->wafat_hijriah,
                'source' => $teacher->source,
                'created_at' => $teacher->created_at,
                'updated_at' => $teacher->updated_at,
            ]);

            // Delete from teachers? Maybe not, safer to keep.
            // But if we reverse, we expect to undo the merge.
            // Let's delete them from teachers to complete the reversal.
            DB::table('teachers')->where('id', $teacher->id)->delete();
        }

        // Drop maps column from teachers
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('maps');
        });
    }
};
