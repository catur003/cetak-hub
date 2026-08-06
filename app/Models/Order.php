<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending', 'menunggu_verifikasi', 'dibayar',
        'diproses', 'dicetak', 'selesai', 'dibatalkan',
    ];

    // Alur status yang VALID (dari => [tujuan yg diperbolehkan])
    // Dipakai OrderService buat cegah lompat status sembarangan (mis. pending langsung ke selesai)
    public const TRANSITIONS = [
        'pending' => ['menunggu_verifikasi', 'dibayar', 'dibatalkan'],
        'menunggu_verifikasi' => ['dibayar', 'dibatalkan'],
        'dibayar' => ['diproses', 'dibatalkan'],
        'diproses' => ['dicetak'],
        'dicetak' => ['selesai'],
        'selesai' => [],
        'dibatalkan' => [],
    ];

    protected $fillable = [
        'order_number', 'user_id', 'status', 'total_price',
        'shipping_address', 'notes', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class)->latest();
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::TRANSITIONS[$this->status] ?? []);
    }
}
