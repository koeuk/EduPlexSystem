<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('department')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('department');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
