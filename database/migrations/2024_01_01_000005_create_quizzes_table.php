<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('quiz_title');
            $table->text('instructions')->nullable();
            $table->integer('time_limit_minutes')->nullable();
            $table->decimal('passing_score', 5, 2)->default(70.00);
            $table->integer('max_attempts')->nullable();
            $table->boolean('show_correct_answers')->default(false);
            $table->boolean('randomize_questions')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
