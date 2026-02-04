<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->constrained('course_modules')->nullOnDelete();
            $table->string('lesson_title');
            $table->enum('lesson_type', ['video', 'text', 'quiz', 'assignment', 'document'])->default('text');
            $table->integer('lesson_order')->default(0);
            $table->text('description')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->longText('content')->nullable();
            $table->integer('video_duration')->nullable()->comment('Duration in seconds');
            $table->foreignId('quiz_id')->nullable()->constrained('quizzes')->nullOnDelete();
            $table->boolean('is_mandatory')->default(false);
            $table->integer('duration_minutes')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'lesson_order']);
            $table->index(['module_id', 'lesson_order']);
            $table->index('lesson_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
