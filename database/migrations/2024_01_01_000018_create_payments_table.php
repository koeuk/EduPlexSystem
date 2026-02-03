<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->unique()->nullable();
            $table->timestamp('payment_date')->useCurrent();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');

            $table->index('status');
            $table->index('payment_date');
            $table->index(['student_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
