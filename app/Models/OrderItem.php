<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_variant_id', 'qty', 'price_per_unit',
        'subtotal', 'design_file_url', 'design_file_public_id', 'item_notes',
    ];

    protected function casts(): array
    {
        return [
            'price_per_unit' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
