@extends('layouts.storefront')

@section('title', 'Keranjang - CetakPro')

@section('content')
<h1 class="text-xl font-bold text-slate-800 mb-6">Keranjang Belanja</h1>

@if ($items->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center">
        <p class="text-slate-400 mb-4">Keranjang kamu masih kosong.</p>
        <a href="{{ route('storefront.products.index') }}" class="text-indigo-600 font-medium hover:text-indigo-700">Lihat Produk &rarr;</a>
    </div>
@else
    <div class="bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100">
        @foreach ($items as $item)
            <div class="p-4 flex items-center gap-4">
                @if ($item->variant->product->thumbnail_url)
                    <img src="{{ $item->variant->product->thumbnail_url }}" class="w-16 h-16 rounded-lg object-cover border border-slate-200" alt="">
                @else
                    <div class="w-16 h-16 rounded-lg bg-slate-100"></div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-slate-800 truncate">{{ $item->variant->product->name }}</p>
                    <p class="text-xs text-slate-400">{{ trim(($item->variant->ukuran ?? '') . ' ' . ($item->variant->bahan ?? '')) ?: 'Standar' }}</p>
                    @if ($item->notes)
                        <p class="text-xs text-slate-400">Catatan: {{ $item->notes }}</p>
                    @endif
                    <p class="text-sm font-semibold text-indigo-600 mt-1">Rp {{ number_format($item->variant->price, 0, ',', '.') }}</p>
                </div>
                <form method="POST" action="{{ route('cart.update', $item->variant->id) }}" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <input type="number" name="qty" value="{{ $item->qty }}" min="{{ $item->variant->min_order }}"
                           class="w-16 border border-slate-300 rounded-lg px-2 py-1.5 text-sm text-center">
                    <button type="submit" class="text-xs text-indigo-600 font-medium hover:text-indigo-700">Update</button>
                </form>
                <p class="w-28 text-right font-semibold text-slate-800 text-sm">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                <form method="POST" action="{{ route('cart.destroy', $item->variant->id) }}" onsubmit="return confirm('Hapus item ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500 hover:text-red-600 text-xs font-medium">Hapus</button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="mt-6 bg-white rounded-2xl border border-slate-200 p-5 flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500">Total</p>
            <p class="text-xl font-bold text-slate-800">Rp {{ number_format($total, 0, ',', '.') }}</p>
        </div>
        <a href="{{ route('checkout.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-lg">
            Checkout
        </a>
    </div>
@endif
@endsection
