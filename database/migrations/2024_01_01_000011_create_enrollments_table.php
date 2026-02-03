<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->timestamp('enrollment_date')->useCurrent();
            $table->timestamp('completion_date')->nullable();
            $table->decimal('progress_percentage', 5, 2)->default(0.00);
            $table->enum('status', ['active', 'completed', 'dropped', 'expired'])->default('active');
            $table->timestamp('last_accessed')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->boolean('certificate_issued')->default(false);

            $table->unique(['student_id', 'course_id']);
            $table->index('status');
            $table->index('payment_status');
            $table->index('enrollment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
