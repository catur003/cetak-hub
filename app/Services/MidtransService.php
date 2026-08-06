<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Midtrans\Config;
use Midtrans\Snap;

/**
 * Wrapper Midtrans Snap - API getSnapToken() dikonfirmasi dari dokumentasi
 * resmi midtrans/midtrans-php (github.com/Midtrans/midtrans-php), formula
 * signature_key dikonfirmasi dari docs.midtrans.com/docs/https-notification-webhooks
 * (dicek 6 Agustus 2026): sha512(order_id + status_code + gross_amount + ServerKey).
 *
 * BELUM PERNAH DITES ke akun Midtrans sandbox asli project ini - WAJIB 1x
 * transaksi test sebelum dianggap final, terutama alur webhook.
 */
class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = (bool) config('midtrans.is_sanitized');
        Config::$is3ds = (bool) config('midtrans.is_3ds');
    }

    /**
     * Bikin Snap token buat 1 Order. order_id yang dikirim ke Midtrans PAKAI
     * PREFIX + Order->id (bukan order_number apa adanya) supaya kalau
     * customer checkout ulang buat order yang sama, order_id ke Midtrans
     * tetap unik (Midtrans nolak order_id yang sama dipakai 2x).
     */
    public function createSnapToken(Order $order): string
    {
        $midtransOrderId = 'CP-' . $order->id . '-' . now()->timestamp;

        $params = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => (int) round($order->total_price),
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->user->phone,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'method' => 'midtrans',
                'status' => 'pending',
                'amount' => $order->total_price,
                'midtrans_order_id' => $midtransOrderId,
                'snap_token' => $snapToken,
            ]
        );

        return $snapToken;
    }

    /**
     * Verifikasi signature_key dari payload webhook - JANGAN PERNAH percaya
     * payload webhook tanpa ini (siapapun bisa POST ke endpoint webhook,
     * signature_key yang buktiin itu beneran dari Midtrans, bukan orang lain
     * ngaku-ngaku transaksi sukses buat dapet barang gratis).
     */
    public function verifySignature(array $payload): bool
    {
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . config('midtrans.server_key'));

        return hash_equals($expected, $signatureKey);
    }
}
