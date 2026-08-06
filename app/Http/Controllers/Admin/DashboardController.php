<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCustomers = User::role('customer')->where('is_active', true)->count();

        $revenueThisMonth = Order::whereIn('status', ['dibayar', 'diproses', 'dicetak', 'selesai'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_price');

        $revenueLastMonth = Order::whereIn('status', ['dibayar', 'diproses', 'dicetak', 'selesai'])
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('total_price');

        $growthPercent = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : 0;

        $statusCounts = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Tren 30 hari terakhir buat line chart
        $dailyRevenue = Order::selectRaw('DATE(created_at) as date, SUM(total_price) as total')
            ->whereIn('status', ['dibayar', 'diproses', 'dicetak', 'selesai'])
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $recentOrders = Order::with('user')->latest()->take(5)->get();

        return view('admin.dashboard.index', compact(
            'totalCustomers',
            'revenueThisMonth',
            'growthPercent',
            'statusCounts',
            'dailyRevenue',
            'recentOrders'
        ));
    }
}
