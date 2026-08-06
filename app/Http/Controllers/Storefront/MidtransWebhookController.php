<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\MidtransService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtrans,
        private readonly OrderService $orderService,
    ) {
    }

    /**
     * Endpoint publik dipanggil MIDTRANS (bukan customer/admin) - route ini
     * WAJIB dikecualikan dari CSRF (lihat bootstrap/app.php) karena Midtrans
     * gak ngirim CSRF token Laravel. Proteksinya BUKAN dari CSRF, tapi dari
     * signature_key verification di bawah - JANGAN PERNAH hapus verifySignature
     * ini dengan alasan apapun, itu satu-satunya yang buktiin request beneran
     * dari Midtrans bukan orang iseng nge-hit endpoint ini manual.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (! $this->midtrans->verifySignature($payload)) {
            Log::warning('Midtrans webhook: signature gak valid', ['order_id' => $payload['order_id'] ?? null]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $midtransOrderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        $payment = Payment::where('midtrans_order_id', $midtransOrderId)->first();
        if (! $payment) {
            // Bisa aja notifikasi telat/duplikat buat order lama yang udah
            // gak ada datanya - balikin 200 (bukan error) biar Midtrans gak
            // retry-retry terus nge-hit endpoint ini percuma.
            Log::info('Midtrans webhook: payment gak ketemu', ['midtrans_order_id' => $midtransOrderId]);
            return response()->json(['message' => 'OK']);
        }

        // Idempotent - kalau udah paid, gak perlu diproses ulang (Midtrans bisa
        // kirim notifikasi sama lebih dari 1x).
        if ($payment->status === 'paid') {
            return response()->json(['message' => 'OK']);
        }

        DB::transaction(function () use ($payment, $transactionStatus, $fraudStatus, $payload) {
            $payment->update(['midtrans_raw_response' => $payload]);

            if (in_array($transactionStatus, ['capture', 'settlement'], true) && $fraudStatus !== 'deny') {
                $payment->update([
                    'status' => 'paid',
                    'midtrans_transaction_id' => $payload['transaction_id'] ?? null,
                ]);
                try {
                    $this->orderService->changeStatus($payment->order, 'dibayar', 'Pembayaran Midtrans terverifikasi otomatis.');
                } catch (\Illuminate\Validation\ValidationException $e) {
                    Log::warning('Midtrans webhook: gagal ubah status order', ['order_id' => $payment->order_id, 'error' => $e->getMessage()]);
                }
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'], true)) {
                $payment->update(['status' => $transactionStatus === 'expire' ? 'expired' : 'failed']);
            }
            // 'pending' - biarin apa adanya, belum ada keputusan final dari Midtrans.
        });

        return response()->json(['message' => 'OK']);
    }
}
