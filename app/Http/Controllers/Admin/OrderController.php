<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function index(Request $request): View
    {
        // Eager load user (dipake di tabel) - N+1 kalau lupa. Filter status
        // OPSIONAL lewat query string, validasi terhadap whitelist Order::STATUSES
        // (bukan terima string bebas ke query WHERE).
        $status = $request->query('status');
        if ($status && ! in_array($status, Order::STATUSES, true)) {
            $status = null;
        }

        $orders = Order::with('user')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => Order::STATUSES,
            'activeStatus' => $status,
        ]);
    }

    public function show(Order $order): View
    {
        // Eager load semua relasi yang dipake view sekaligus - jangan biarin
        // Blade lazy-load 1-1 di dalam loop (N+1 klasik).
        $order->load(['user', 'items.variant.product', 'payment', 'statusLogs.changedBy']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', Order::STATUSES)],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->orderService->changeStatus($order, $validated['status'], $validated['note'] ?? null);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
