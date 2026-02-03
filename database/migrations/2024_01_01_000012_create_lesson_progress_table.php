<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->decimal('progress_percentage', 5, 2)->default(0.00);
            $table->integer('time_spent_minutes')->default(0);
            $table->integer('video_last_position')->nullable()->comment('Position in seconds');
            $table->integer('scroll_position')->nullable();
            $table->timestamp('first_accessed')->nullable();
            $table->timestamp('last_accessed')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->unique(['student_id', 'lesson_id']);
            $table->index(['student_id', 'course_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
    }
};
