<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // generated: CP-20260806-XXXX
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', [
                'pending',              // baru dibuat, belum bayar
                'menunggu_verifikasi',  // upload bukti manual, nunggu admin cek
                'dibayar',              // pembayaran lunas/terverifikasi
                'diproses',             // masuk produksi
                'dicetak',              // selesai cetak, siap kirim
                'selesai',              // sudah diterima customer
                'dibatalkan',
            ])->default('pending');
            $table->decimal('total_price', 12, 2);
            $table->text('shipping_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
