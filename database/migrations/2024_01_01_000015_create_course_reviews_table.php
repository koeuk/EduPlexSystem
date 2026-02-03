<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->tinyInteger('rating')->unsigned();
            $table->text('review_text')->nullable();
            $table->boolean('would_recommend')->default(true);
            $table->timestamps();

            $table->unique(['course_id', 'student_id']);
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_reviews');
    }
};
