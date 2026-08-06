@extends('layouts.admin')

@section('title', 'Detail Pesanan')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800">{{ $order->order_number }}</h2>
        <p class="text-slate-500 text-sm">{{ $order->created_at->format('d M Y, H:i') }}</p>
    </div>
    <span class="px-3 py-1.5 rounded-full text-sm bg-indigo-50 text-indigo-600 capitalize font-medium">
        {{ str_replace('_', ' ', $order->status) }}
    </span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Item pesanan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-800 mb-4">Item Pesanan</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-400 border-b border-slate-100">
                        <th class="pb-2 font-medium">Produk</th>
                        <th class="pb-2 font-medium">Qty</th>
                        <th class="pb-2 font-medium">Harga</th>
                        <th class="pb-2 font-medium">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="border-b border-slate-50">
                            <td class="py-3 text-slate-700">
                                {{ $item->variant->product->name ?? '-' }}
                                <span class="text-slate-400 text-xs block">{{ $item->variant->ukuran ?? '' }} {{ $item->variant->bahan ?? '' }}</span>
                                @if ($item->item_notes)
                                    <span class="text-slate-400 text-xs block">Catatan: {{ $item->item_notes }}</span>
                                @endif
                            </td>
                            <td class="py-3 text-slate-600">{{ $item->qty }}</td>
                            <td class="py-3 text-slate-600">Rp {{ number_format($item->price_per_unit, 0, ',', '.') }}</td>
                            <td class="py-3 text-slate-700 font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="pt-3 text-right font-semibold text-slate-700">Total</td>
                        <td class="pt-3 font-bold text-slate-800">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Riwayat status --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-800 mb-4">Riwayat Status</h3>
            <div class="space-y-3">
                @forelse ($order->statusLogs as $log)
                    <div class="flex items-start gap-3 text-sm">
                        <div class="w-2 h-2 rounded-full bg-indigo-500 mt-1.5 flex-shrink-0"></div>
                        <div>
                            <p class="text-slate-700">
                                <span class="capitalize">{{ str_replace('_', ' ', $log->from_status ?? 'baru dibuat') }}</span>
                                &rarr;
                                <span class="capitalize font-medium">{{ str_replace('_', ' ', $log->to_status) }}</span>
                            </p>
                            <p class="text-xs text-slate-400">
                                {{ $log->created_at->format('d M Y H:i') }} oleh {{ $log->changedBy->name ?? 'Sistem' }}
                                @if ($log->note) - {{ $log->note }} @endif
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400 text-sm">Belum ada riwayat.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Customer --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-800 mb-3">Customer</h3>
            <p class="text-sm text-slate-700 font-medium">{{ $order->user->name ?? '-' }}</p>
            <p class="text-sm text-slate-500">{{ $order->user->email ?? '-' }}</p>
            <p class="text-sm text-slate-500">{{ $order->user->phone ?? '-' }}</p>
            @if ($order->shipping_address)
                <p class="text-sm text-slate-500 mt-2 pt-2 border-t border-slate-100">{{ $order->shipping_address }}</p>
            @endif
            @if ($order->notes)
                <p class="text-sm text-slate-500 mt-2">Catatan: {{ $order->notes }}</p>
            @endif
        </div>

        {{-- Pembayaran --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <h3 class="font-semibold text-slate-800 mb-3">Pembayaran</h3>
            @if ($order->payment)
                <p class="text-sm text-slate-600">Metode: <span class="font-medium text-slate-800">{{ $order->payment->method }}</span></p>
                <p class="text-sm text-slate-600">Status: <span class="font-medium text-slate-800 capitalize">{{ str_replace('_', ' ', $order->payment->status) }}</span></p>
                <p class="text-sm text-slate-600">Jumlah: Rp {{ number_format($order->payment->amount, 0, ',', '.') }}</p>
                @if ($order->payment->method === 'manual_transfer' && $order->payment->status === 'menunggu_verifikasi')
                    <a href="{{ route('admin.payments.index') }}" class="inline-block mt-2 text-sm font-medium text-indigo-600 hover:text-indigo-700">
                        Verifikasi di halaman Pembayaran &rarr;
                    </a>
                @endif
            @else
                <p class="text-sm text-slate-400">Belum ada data pembayaran.</p>
            @endif
        </div>

        {{-- Ubah status --}}
        @php $nextOptions = \App\Models\Order::TRANSITIONS[$order->status] ?? []; @endphp
        @if (count($nextOptions) > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-semibold text-slate-800 mb-3">Ubah Status</h3>
                <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="">Pilih status baru</option>
                        @foreach ($nextOptions as $option)
                            <option value="{{ $option }}" class="capitalize">{{ str_replace('_', ' ', $option) }}</option>
                        @endforeach
                    </select>
                    <textarea name="note" rows="2" placeholder="Catatan (opsional)"
                              class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg">
                        Perbarui Status
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
