<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->integer('attempt_number')->default(1);
            $table->decimal('score_percentage', 5, 2)->nullable();
            $table->integer('total_points')->default(0);
            $table->integer('max_points')->default(0);
            $table->boolean('passed')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('submitted_at')->nullable();
            $table->integer('time_taken_minutes')->nullable();

            $table->index(['quiz_id', 'student_id']);
            $table->index('passed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
