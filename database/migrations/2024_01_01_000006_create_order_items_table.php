<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $table->integer('qty');
            $table->decimal('price_per_unit', 12, 2); // snapshot harga saat order (jangan andalkan harga produk yg bisa berubah)
            $table->decimal('subtotal', 12, 2);
            $table->string('design_file_url')->nullable();     // Cloudinary URL
            $table->string('design_file_public_id')->nullable();
            $table->text('item_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
