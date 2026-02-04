<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('enrollment_code', 20)->unique()->nullable()->after('course_code');
        });

        // Generate enrollment codes for existing courses
        $courses = DB::table('courses')->get();
        foreach ($courses as $course) {
            DB::table('courses')
                ->where('id', $course->id)
                ->update(['enrollment_code' => strtoupper(Str::random(8))]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('enrollment_code');
        });
    }
};
