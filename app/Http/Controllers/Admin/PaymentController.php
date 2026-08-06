<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function index(Request $request): View
    {
        $status = $request->query('status', 'menunggu_verifikasi');

        $payments = Payment::with('order.user')
            ->when($status && $status !== 'semua', fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.payments.index', compact('payments', 'status'));
    }

    /**
     * Verifikasi transfer manual - SATU-SATUNYA jalur ubah status Payment jadi
     * 'paid' buat metode manual_transfer. Bukan sekadar update kolom, ini juga
     * yang trigger Order ikut pindah status via OrderService (audit trail
     * kejaga, gak ada 2 sumber kebenaran status order).
     */
    public function verify(Payment $payment): RedirectResponse
    {
        if ($payment->method !== 'manual_transfer') {
            return back()->with('error', 'Verifikasi manual cuma buat metode transfer manual.');
        }
        if ($payment->status === 'paid') {
            return back()->with('error', 'Pembayaran ini udah diverifikasi sebelumnya.');
        }

        try {
            DB::transaction(function () use ($payment) {
                $payment->update([
                    'status' => 'paid',
                    'verified_by' => Auth::id(),
                    'verified_at' => now(),
                ]);

                $this->orderService->changeStatus($payment->order, 'dibayar', 'Diverifikasi manual oleh admin.');
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return back()->with('success', 'Pembayaran berhasil diverifikasi, order dipindah ke status dibayar.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $payment->update(['status' => 'failed']);

        try {
            $this->orderService->changeStatus($payment->order, 'dibatalkan', 'Bukti transfer ditolak: ' . $validated['reason']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Order mungkin udah di status yang gak bisa dibatalin lagi (mis. udah diproses) -
            // Payment tetap ditandai failed, tapi kasih tau adminnya kenapa order gak ikut berubah.
            return back()->with('error', 'Pembayaran ditolak, TAPI status order gagal diubah: ' . $e->validator->errors()->first());
        }

        return back()->with('success', 'Pembayaran ditolak, order dibatalkan.');
    }
}
