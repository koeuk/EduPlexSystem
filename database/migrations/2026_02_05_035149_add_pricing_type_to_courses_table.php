<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('pricing_type')->default('paid')->after('level');
        });

        // Set existing courses: if price > 0 then 'paid', else 'free'
        DB::table('courses')
            ->where('price', '>', 0)
            ->update(['pricing_type' => 'paid']);

        DB::table('courses')
            ->where('price', '<=', 0)
            ->update(['pricing_type' => 'free']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('pricing_type');
        });
    }
};
