<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('student_id_number')->unique();
            $table->date('enrollment_date')->nullable();
            $table->enum('student_status', ['active', 'inactive', 'graduated', 'suspended'])->default('active');
            $table->string('image_url', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('student_status');
            $table->index('enrollment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
