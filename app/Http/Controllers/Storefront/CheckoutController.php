<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\CartService;
use App\Services\MidtransService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly OrderService $orderService,
    ) {
    }

    public function index(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        return view('storefront.checkout.index', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    /**
     * Bikin Order dari isi keranjang. HARGA DIAMBIL LIVE dari ProductVariant
     * saat ini (bukan dari input form/session) - customer gak bisa manipulasi
     * harga lewat request meski cuma edit HTML/network tab, karena harga
     * final dihitung ulang di server dari data DB.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        $validated = $request->validate([
            'shipping_address' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:midtrans,manual_transfer'],
        ]);

        $items = $this->cart->items();
        $total = $items->sum('subtotal');

        $order = DB::transaction(function () use ($items, $total, $validated) {
            $order = Order::create([
                'order_number' => $this->orderService->generateOrderNumber(),
                'user_id' => Auth::id(),
                'status' => 'pending',
                'total_price' => $total,
                'shipping_address' => $validated['shipping_address'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item->variant->id,
                    'qty' => $item->qty,
                    'price_per_unit' => $item->variant->price,
                    'subtotal' => $item->subtotal,
                    'item_notes' => $item->notes,
                ]);
            }

            return $order;
        });

        $this->cart->clear();

        if ($validated['payment_method'] === 'manual_transfer') {
            Payment::create([
                'order_id' => $order->id,
                'method' => 'manual_transfer',
                'status' => 'pending',
                'amount' => $total,
            ]);

            return redirect()->route('checkout.payment', $order)->with('success', 'Pesanan dibuat. Silakan lakukan transfer & upload bukti.');
        }

        // Midtrans - redirect ke halaman pembayaran, Snap token digenerate di sana
        // (bukan di sini) supaya kalau token expired, customer bisa refresh
        // halaman itu buat generate token baru tanpa bikin order duplikat.
        return redirect()->route('checkout.payment', $order);
    }

    public function payment(Order $order): View
    {
        // Ownership check EKSPLISIT - route model binding cuma mastiin Order
        // dengan ID itu ADA, gak otomatis cek itu punya user yang lagi login.
        // Tanpa ini, user A bisa lihat/bayar order user B cuma dengan tebak ID.
        abort_unless($order->user_id === Auth::id(), 403);

        $order->load('payment');

        $snapToken = null;
        if (! $order->payment || $order->payment->method === 'midtrans') {
            if (in_array($order->status, ['pending'], true) && (! $order->payment || $order->payment->status === 'pending')) {
                $snapToken = app(MidtransService::class)->createSnapToken($order);
            }
        }

        return view('storefront.checkout.payment', compact('order', 'snapToken'));
    }

    public function uploadProof(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'proof' => ['required', 'image', 'max:2048'],
            'bank_sender' => ['required', 'string', 'max:255'],
        ]);

        $order->loadMissing('payment');
        if (! $order->payment || $order->payment->method !== 'manual_transfer') {
            abort(404);
        }

        $uploader = app(\App\Services\CloudinaryUploadService::class);
        $uploaded = $uploader->upload($request->file('proof'), 'cetakpro/payment-proofs');

        $order->payment->update([
            'status' => 'menunggu_verifikasi',
            'proof_url' => $uploaded['url'],
            'proof_public_id' => $uploaded['public_id'],
            'bank_sender' => $validated['bank_sender'],
        ]);

        try {
            $this->orderService->changeStatus($order, 'menunggu_verifikasi', 'Bukti transfer diupload customer.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Order mungkin udah ke-cancel/dibatalin di antara load & update ini -
            // bukti tetap tersimpan (gak ilang), tapi status order gak dipaksa berubah.
        }

        return redirect()->route('checkout.success', $order);
    }

    public function success(Order $order): View
    {
        abort_unless($order->user_id === Auth::id(), 403);

        return view('storefront.checkout.success', compact('order'));
    }
}
