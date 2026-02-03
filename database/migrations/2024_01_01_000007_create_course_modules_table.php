<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('module_title');
            $table->integer('module_order')->default(0);
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['course_id', 'module_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_modules');
    }
};
