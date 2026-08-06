<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - CetakPro Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.5/cdn.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- FIX (6 Agustus 2026, arahan user): opacity/glass effect dihapus - sidebar
     & topbar sekarang SOLID putih (bg-white biasa), bukan bg-white/70
     backdrop-blur lagi. --}}
<body class="bg-slate-50 min-h-screen">

    <div class="flex min-h-screen">
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed lg:static z-40 w-64 min-h-screen bg-white border-r border-slate-200 p-4 transition-transform duration-200 flex flex-col"
        >
            <div class="flex items-center gap-3 px-2 py-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold text-base">
                    CP
                </div>
                <div>
                    <p class="font-bold text-slate-800 leading-tight text-sm">CetakPro</p>
                    <p class="text-xs text-slate-400">Admin Panel</p>
                </div>
            </div>

            <nav class="mt-6 flex-1 space-y-1">
                @php
                    $navItems = [
                        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'grid'],
                        ['label' => 'Kategori', 'route' => 'admin.categories.index', 'icon' => 'tag'],
                        ['label' => 'Produk', 'route' => 'admin.products.index', 'icon' => 'box'],
                        ['label' => 'Pesanan', 'route' => 'admin.orders.index', 'icon' => 'bag'],
                        ['label' => 'Pembayaran', 'route' => 'admin.payments.index', 'icon' => 'card'],
                        ['label' => 'Customer', 'route' => 'admin.customers.index', 'icon' => 'users'],
                    ];

                    $icons = [
                        'grid' => 'M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z',
                        'tag' => 'M7 7h.01M3 11l7.59-7.59A2 2 0 0112.17 3H19a2 2 0 012 2v6.83a2 2 0 01-.59 1.41L12.83 21a2 2 0 01-2.83 0L3 13.83a2 2 0 010-2.83z',
                        'box' => 'M20 7L12 3 4 7m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                        'bag' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 11H4L5 9z',
                        'card' => 'M3 10h18M7 15h1m4 0h1M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z',
                        'users' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-4.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4',
                    ];
                @endphp

                @foreach ($navItems as $item)
                    @php $active = request()->routeIs($item['route'] . '*'); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                              {{ $active
                                    ? 'bg-indigo-600 text-white'
                                    : 'text-slate-600 hover:bg-slate-100' }}">
                        <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="{{ $icons[$item['icon']] }}" />
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-slate-200 pt-4 mt-4">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-sm flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-xs text-slate-400">Admin</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-slate-400 hover:text-red-500" title="Logout" aria-label="Logout">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
             class="fixed inset-0 bg-black/30 z-30 lg:hidden"></div>

        <div class="flex-1 min-w-0">
            <header class="sticky top-0 z-20 bg-white border-b border-slate-200 px-4 sm:px-6 py-3.5 flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-600" aria-label="Menu">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="flex-1">
                    <h1 class="text-lg font-bold text-slate-800">@yield('title', 'Dashboard')</h1>
                </div>
            </header>

            <main class="p-4 sm:p-6">
                @if (session('success'))
                    <div class="mb-4 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-2.5">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-2.5">
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>
