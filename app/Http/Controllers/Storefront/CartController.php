<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(): View
    {
        return view('storefront.cart.index', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'qty' => ['required', 'integer', 'min:1', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $variant = ProductVariant::findOrFail($validated['variant_id']);

        if (! $variant->is_active) {
            return back()->with('error', 'Varian ini sudah tidak tersedia.');
        }
        if ($validated['qty'] < $variant->min_order) {
            return back()->with('error', "Minimal order buat varian ini {$variant->min_order} pcs.");
        }

        $this->cart->add($variant->id, $validated['qty'], $validated['notes'] ?? null);

        return back()->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, int $variantId): RedirectResponse
    {
        $validated = $request->validate(['qty' => ['required', 'integer', 'min:0', 'max:1000']]);
        $this->cart->updateQty($variantId, $validated['qty']);

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function destroy(int $variantId): RedirectResponse
    {
        $this->cart->remove($variantId);

        return back()->with('success', 'Item dihapus dari keranjang.');
    }
}
