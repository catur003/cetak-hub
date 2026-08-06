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
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('method', ['midtrans', 'manual_transfer'])->default('manual_transfer');
            $table->enum('status', ['pending', 'menunggu_verifikasi', 'paid', 'failed', 'expired'])->default('pending');
            $table->decimal('amount', 12, 2);

            // Midtrans fields
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('snap_token')->nullable();
            $table->json('midtrans_raw_response')->nullable();

            // Manual transfer fields
            $table->string('proof_url')->nullable();      // Cloudinary URL bukti transfer
            $table->string('proof_public_id')->nullable();
            $table->string('bank_sender')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
