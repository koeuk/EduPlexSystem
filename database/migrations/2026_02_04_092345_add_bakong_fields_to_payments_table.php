<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('bakong_transaction_id')->nullable()->after('transaction_id');
            $table->text('qr_string')->nullable()->after('bakong_transaction_id');
            $table->string('md5_hash')->nullable()->after('qr_string');
            $table->timestamp('qr_expires_at')->nullable()->after('md5_hash');
            $table->string('bakong_status')->nullable()->after('qr_expires_at'); // PENDING, SUCCESS, FAILED, EXPIRED
            $table->json('bakong_response')->nullable()->after('bakong_status');

            $table->index('bakong_transaction_id');
            $table->index('md5_hash');
        });

        // Update payment_method enum to include bakong
        // Note: In production, you might want to handle this differently
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['bakong_transaction_id']);
            $table->dropIndex(['md5_hash']);
            $table->dropColumn([
                'bakong_transaction_id',
                'qr_string',
                'md5_hash',
                'qr_expires_at',
                'bakong_status',
                'bakong_response',
            ]);
        });
    }
};
