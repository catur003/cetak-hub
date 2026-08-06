@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<p class="text-slate-500 mb-6">Selamat datang kembali, berikut ringkasan hari ini.</p>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    @php
        $cards = [
            ['label' => 'Total Customer', 'value' => number_format($totalCustomers), 'icon' => '👥', 'bg' => 'bg-indigo-100'],
            ['label' => 'Omzet Bulan Ini', 'value' => 'Rp '.number_format($revenueThisMonth, 0, ',', '.'), 'icon' => '💰', 'bg' => 'bg-emerald-100'],
            ['label' => 'Pesanan Diproses', 'value' => $statusCounts->get('diproses', 0), 'icon' => '🖨️', 'bg' => 'bg-amber-100'],
            ['label' => 'Menunggu Verifikasi', 'value' => $statusCounts->get('menunggu_verifikasi', 0), 'icon' => '⏳', 'bg' => 'bg-rose-100'],
        ];
    @endphp

    @foreach ($cards as $card)
        <div class="bg-white/80 backdrop-blur rounded-2xl p-5 shadow-sm border border-white/60">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-slate-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $card['value'] }}</p>
                </div>
                <div class="w-11 h-11 rounded-full {{ $card['bg'] }} flex items-center justify-center text-lg">
                    {{ $card['icon'] }}
                </div>
            </div>
            @if ($card['label'] === 'Omzet Bulan Ini')
                <p class="text-xs mt-2 {{ $growthPercent >= 0 ? 'text-emerald-600' : 'text-rose-500' }}">
                    {{ $growthPercent >= 0 ? '↑' : '↓' }} {{ abs($growthPercent) }}% dari bulan lalu
                </p>
            @endif
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    {{-- Line chart --}}
    <div class="xl:col-span-2 bg-white/80 backdrop-blur rounded-2xl p-5 shadow-sm border border-white/60">
        <h2 class="font-semibold text-slate-800 mb-4">Tren Omzet (30 Hari)</h2>
        <canvas id="revenueChart" height="110"></canvas>
    </div>

    {{-- Donut status --}}
    <div class="bg-white/80 backdrop-blur rounded-2xl p-5 shadow-sm border border-white/60">
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

{{-- Recent orders --}}
<div class="bg-white/80 backdrop-blur rounded-2xl p-5 shadow-sm border border-white/60 overflow-x-auto">
    <h2 class="font-semibold text-slate-800 mb-4">Pesanan Terbaru</h2>
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
                    <td class="py-3 font-medium text-slate-700">{{ $order->order_number }}</td>
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
