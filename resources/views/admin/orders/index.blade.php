@extends('layouts.admin')

@section('title', 'Pesanan')

@section('content')
<div class="flex flex-wrap items-center gap-2 mb-6">
    <a href="{{ route('admin.orders.index') }}"
       class="px-3 py-1.5 rounded-full text-xs font-medium {{ ! $activeStatus ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
        Semua
    </a>
    @foreach ($statuses as $status)
        <a href="{{ route('admin.orders.index', ['status' => $status]) }}"
           class="px-3 py-1.5 rounded-full text-xs font-medium capitalize {{ $activeStatus === $status ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
            {{ str_replace('_', ' ', $status) }}
        </a>
    @endforeach
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-x-auto">
    <table class="w-full text-sm min-w-[700px]">
        <thead>
            <tr class="text-left text-slate-400 border-b border-slate-100">
                <th class="px-5 py-3 font-medium">No. Order</th>
                <th class="px-5 py-3 font-medium">Customer</th>
                <th class="px-5 py-3 font-medium">Total</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Tanggal</th>
                <th class="px-5 py-3 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr class="border-b border-slate-50">
                    <td class="px-5 py-3 font-medium text-slate-700">{{ $order->order_number }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $order->user->name ?? '-' }}</td>
                    <td class="px-5 py-3 text-slate-600">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2.5 py-1 rounded-full text-xs bg-indigo-50 text-indigo-600 capitalize">
                            {{ str_replace('_', ' ', $order->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-slate-500">{{ $order->created_at->format('d M Y H:i') }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-slate-400">Belum ada pesanan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $orders->links() }}</div>
@endsection
