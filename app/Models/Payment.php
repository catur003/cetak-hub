<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'method', 'status', 'amount',
        'midtrans_order_id', 'midtrans_transaction_id', 'snap_token', 'midtrans_raw_response',
        'proof_url', 'proof_public_id', 'bank_sender', 'verified_by', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'midtrans_raw_response' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
