@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<p class="text-slate-500 mb-6">Selamat datang kembali, berikut ringkasan hari ini.</p>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    @php
        $cards = [
            ['label' => 'Total Customer', 'value' => number_format($totalCustomers), 'icon' => 'users', 'bg' => 'bg-indigo-50', 'fg' => 'text-indigo-600'],
            ['label' => 'Omzet Bulan Ini', 'value' => 'Rp '.number_format($revenueThisMonth, 0, ',', '.'), 'icon' => 'money', 'bg' => 'bg-emerald-50', 'fg' => 'text-emerald-600'],
            ['label' => 'Pesanan Diproses', 'value' => $statusCounts->get('diproses', 0), 'icon' => 'printer', 'bg' => 'bg-amber-50', 'fg' => 'text-amber-600'],
            ['label' => 'Menunggu Verifikasi', 'value' => $statusCounts->get('menunggu_verifikasi', 0), 'icon' => 'clock', 'bg' => 'bg-rose-50', 'fg' => 'text-rose-600'],
        ];
        $icons = [
            'users' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-4.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4',
            'money' => 'M12 8c-1.66 0-3 .9-3 2s1.34 2 3 2 3 .9 3 2-1.34 2-3 2m0-8V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z',
            'printer' => 'M6 9V4h12v5M6 18H4a1 1 0 01-1-1v-6a1 1 0 011-1h16a1 1 0 011 1v6a1 1 0 01-1 1h-2M6 14h12v7H6v-7z',
            'clock' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        ];
    @endphp

    @foreach ($cards as $card)
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $card['value'] }}</p>
                </div>
                <div class="w-11 h-11 rounded-full {{ $card['bg'] }} {{ $card['fg'] }} flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="{{ $icons[$card['icon']] }}" />
                    </svg>
                </div>
            </div>
            @if ($card['label'] === 'Omzet Bulan Ini')
                <p class="text-xs mt-2 {{ $growthPercent >= 0 ? 'text-emerald-600' : 'text-rose-500' }}">
                    {{ $growthPercent >= 0 ? 'Naik' : 'Turun' }} {{ abs($growthPercent) }}% dari bulan lalu
                </p>
            @endif
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <div class="xl:col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
        <h2 class="font-semibold text-slate-800 mb-4">Tren Omzet (30 Hari)</h2>
        <canvas id="revenueChart" height="110"></canvas>
    </div>

    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
        <h2 class="font-semibold text-slate-800 mb-4">Status Pesanan</h2>
        <canvas id="statusChart" height="180"></canvas>
        <div class="mt-4 space-y-2 text-sm">
            @foreach ($statusCounts as $status => $count)
                <div class="flex justify-between text-slate-600">
                    <span class="capitalize">{{ str_replace('_', ' ', $status) }}</span>
                    <span class="font-medium">{{ $count }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 overflow-x-auto">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-slate-800">Pesanan Terbaru</h2>
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-indigo-600 font-medium hover:text-indigo-700">Lihat Semua</a>
    </div>
    <table class="w-full text-sm min-w-[600px]">
        <thead>
            <tr class="text-left text-slate-400 border-b border-slate-100">
                <th class="pb-3 font-medium">No. Order</th>
                <th class="pb-3 font-medium">Customer</th>
                <th class="pb-3 font-medium">Total</th>
                <th class="pb-3 font-medium">Status</th>
                <th class="pb-3 font-medium">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($recentOrders as $order)
                <tr class="border-b border-slate-50">
                    <td class="py-3 font-medium text-slate-700">
                        <a href="{{ route('admin.orders.show', $order) }}" class="hover:text-indigo-600">{{ $order->order_number }}</a>
                    </td>
                    <td class="py-3 text-slate-600">{{ $order->user->name ?? '-' }}</td>
                    <td class="py-3 text-slate-600">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                    <td class="py-3">
                        <span class="px-2.5 py-1 rounded-full text-xs bg-indigo-50 text-indigo-600 capitalize">
                            {{ str_replace('_', ' ', $order->status) }}
                        </span>
                    </td>
                    <td class="py-3 text-slate-500">{{ $order->created_at->format('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-6 text-center text-slate-400">Belum ada pesanan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    const revenueLabels = {!! $dailyRevenue->pluck('date')->toJson() !!};
    const revenueData = {!! $dailyRevenue->pluck('total')->toJson() !!};

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                data: revenueData,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 0,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! $statusCounts->keys()->toJson() !!},
            datasets: [{
                data: {!! $statusCounts->values()->toJson() !!},
                backgroundColor: ['#6366f1', '#f59e0b', '#10b981', '#ef4444', '#a855f7', '#94a3b8', '#0ea5e9'],
                borderWidth: 0,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            cutout: '70%'
        }
    });
</script>
@endsection
