@extends('layouts.storefront')

@section('title', 'Pesanan Berhasil - CetakPro')

@section('content')
<div class="max-w-lg mx-auto text-center bg-white rounded-2xl border border-slate-200 p-8">
    <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 13l4 4L19 7" />
        </svg>
    </div>
    <h1 class="text-xl font-bold text-slate-800 mb-2">Pesanan Diterima</h1>
    <p class="text-slate-500 text-sm mb-6">
        Nomor pesanan kamu <span class="font-semibold text-slate-700">{{ $order->order_number }}</span>.
        Status saat ini: <span class="font-medium capitalize">{{ str_replace('_', ' ', $order->status) }}</span>.
    </p>
    <a href="{{ route('storefront.products.index') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-lg">
        Belanja Lagi
    </a>
</div>
@endsection
