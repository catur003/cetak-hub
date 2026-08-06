<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Semua perubahan status order WAJIB lewat service ini.
 * Jangan pernah update $order->status langsung di controller —
 * ini satu-satunya tempat yang validasi alur & bikin audit log,
 * jadi kalau langsung diubah di controller, historinya bakal bolong/ga akurat.
 */
class OrderService
{
    public function changeStatus(Order $order, string $newStatus, ?string $note = null): Order
    {
        if (! $order->canTransitionTo($newStatus)) {
            throw ValidationException::withMessages([
                'status' => "Tidak bisa mengubah status dari '{$order->status}' ke '{$newStatus}'.",
            ]);
        }

        return DB::transaction(function () use ($order, $newStatus, $note) {
            $oldStatus = $order->status;

            $order->update([
                'status' => $newStatus,
                'paid_at' => $newStatus === 'dibayar' ? now() : $order->paid_at,
            ]);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_by' => Auth::id(),
                'note' => $note,
                'created_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    public function generateOrderNumber(): string
    {
        return 'CP-'.now()->format('Ymd').'-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}
