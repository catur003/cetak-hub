@extends('layouts.storefront')

@section('title', 'Checkout - CetakPro')

@section('content')
<h1 class="text-xl font-bold text-slate-800 mb-6">Checkout</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <form method="POST" action="{{ route('checkout.store') }}" class="lg:col-span-2 space-y-6">
        @csrf

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-800 mb-4">Alamat Pengiriman</h2>
            <textarea name="shipping_address" rows="3" required
                      class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                      placeholder="Nama penerima, alamat lengkap, nomor HP">{{ old('shipping_address') }}</textarea>
            @error('shipping_address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

            <label class="block text-sm font-medium text-slate-700 mt-4 mb-1.5">Catatan Tambahan (opsional)</label>
            <textarea name="notes" rows="2"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-800 mb-4">Metode Pembayaran</h2>
            <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg mb-2 cursor-pointer has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                <input type="radio" name="payment_method" value="midtrans" checked class="text-indigo-600 focus:ring-indigo-500">
                <div>
                    <p class="text-sm font-medium text-slate-800">Bayar Online (Midtrans)</p>
                    <p class="text-xs text-slate-400">Kartu kredit, e-wallet, QRIS, virtual account - instan.</p>
                </div>
            </label>
            <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                <input type="radio" name="payment_method" value="manual_transfer" class="text-indigo-600 focus:ring-indigo-500">
                <div>
                    <p class="text-sm font-medium text-slate-800">Transfer Manual</p>
                    <p class="text-xs text-slate-400">Transfer ke rekening toko, upload bukti, diverifikasi admin.</p>
                </div>
            </label>
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-3 rounded-lg">
            Buat Pesanan
        </button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 h-fit">
        <h2 class="font-semibold text-slate-800 mb-4">Ringkasan</h2>
        <div class="space-y-3 mb-4">
            @foreach ($items as $item)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600">{{ $item->variant->product->name }} &times;{{ $item->qty }}</span>
                    <span class="font-medium text-slate-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>
        <div class="border-t border-slate-100 pt-3 flex justify-between">
            <span class="font-semibold text-slate-700">Total</span>
            <span class="font-bold text-slate-800">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
    </div>
</div>
@endsection
