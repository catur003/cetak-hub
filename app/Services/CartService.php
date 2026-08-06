<?php

namespace App\Services;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Keranjang disimpen di SESSION (bukan tabel DB) - sengaja, karena keranjang
 * itu data sementara sebelum jadi Order beneran, gak perlu persist ke DB
 * sampai customer benar-benar checkout.
 *
 * Struktur session: ['variant_id' => ['qty' => int, 'notes' => string|null]]
 */
class CartService
{
    private const SESSION_KEY = 'cart';

    public function add(int $variantId, int $qty, ?string $notes = null): void
    {
        $cart = $this->raw();
        $existingQty = $cart[$variantId]['qty'] ?? 0;

        $cart[$variantId] = [
            'qty' => $existingQty + $qty,
            'notes' => $notes,
        ];

        Session::put(self::SESSION_KEY, $cart);
    }

    public function updateQty(int $variantId, int $qty): void
    {
        $cart = $this->raw();
        if (! isset($cart[$variantId])) {
            return;
        }

        if ($qty <= 0) {
            unset($cart[$variantId]);
        } else {
            $cart[$variantId]['qty'] = $qty;
        }

        Session::put(self::SESSION_KEY, $cart);
    }

    public function remove(int $variantId): void
    {
        $cart = $this->raw();
        unset($cart[$variantId]);
        Session::put(self::SESSION_KEY, $cart);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function raw(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public function isEmpty(): bool
    {
        return count($this->raw()) === 0;
    }

    /**
     * Ambil item keranjang LENGKAP dengan data variant/product TERKINI dari
     * DB (bukan snapshot lama) - harga bisa berubah kapan aja sejak
     * dimasukin ke keranjang, jadi WAJIB re-fetch tiap kali ditampilin,
     * jangan simpen harga di session (bisa jadi stale/salah).
     *
     * Variant yang udah dihapus/dinonaktifkan admin OTOMATIS ke-skip di sini -
     * gak nongol lagi di keranjang tanpa perlu cleanup manual.
     */
    public function items(): Collection
    {
        $cart = $this->raw();
        if (empty($cart)) {
            return collect();
        }

        return ProductVariant::with('product')
            ->whereIn('id', array_keys($cart))
            ->where('is_active', true)
            ->get()
            ->filter(fn ($variant) => $variant->product && $variant->product->is_active)
            ->map(function ($variant) use ($cart) {
                $qty = $cart[$variant->id]['qty'];
                return (object) [
                    'variant' => $variant,
                    'qty' => $qty,
                    'notes' => $cart[$variant->id]['notes'] ?? null,
                    'subtotal' => $variant->price * $qty,
                ];
            })
            ->values();
    }

    public function total(): float
    {
        return $this->items()->sum('subtotal');
    }

    public function count(): int
    {
        return $this->items()->sum('qty');
    }
}
