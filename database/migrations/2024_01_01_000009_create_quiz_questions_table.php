<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->text('question_text');
            $table->enum('question_type', ['multiple_choice', 'true_false', 'short_answer', 'essay'])->default('multiple_choice');
            $table->integer('points')->default(1);
            $table->integer('question_order')->default(0);
            $table->text('explanation')->nullable();

            $table->index(['quiz_id', 'question_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
