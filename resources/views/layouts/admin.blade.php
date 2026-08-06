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
<body class="bg-gradient-to-br from-sky-50 via-indigo-50 to-purple-50 min-h-screen">

    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed lg:static z-40 w-72 min-h-screen bg-white/70 backdrop-blur-xl border-r border-white/50 p-5 transition-transform duration-200 flex flex-col"
        >
            <div class="flex items-center gap-3 px-2 py-3">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-indigo-200">
                    CP
                </div>
                <div>
                    <p class="font-bold text-slate-800 leading-tight">CetakPro</p>
                    <p class="text-xs text-slate-400">Admin Panel</p>
                </div>
            </div>

            <nav class="mt-6 flex-1 space-y-1">
                @php
                    $navItems = [
                        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'grid'],
                        ['label' => 'Produk', 'route' => 'admin.dashboard', 'icon' => 'box'],
                        ['label' => 'Pesanan', 'route' => 'admin.dashboard', 'icon' => 'shopping-bag'],
                        ['label' => 'Pembayaran', 'route' => 'admin.dashboard', 'icon' => 'credit-card'],
                        ['label' => 'Customer', 'route' => 'admin.dashboard', 'icon' => 'users'],
                        ['label' => 'Laporan', 'route' => 'admin.dashboard', 'icon' => 'bar-chart'],
                        ['label' => 'Pengaturan', 'route' => 'admin.dashboard', 'icon' => 'settings'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    @php $active = request()->routeIs($item['route']) && $item['label'] === 'Dashboard'; @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition
                              {{ $active
                                    ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow-lg shadow-indigo-200'
                                    : 'text-slate-600 hover:bg-white/80' }}">
                        <span class="w-2 h-2 rounded-full {{ $active ? 'bg-white' : 'bg-slate-300' }}"></span>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-slate-200/70 pt-4 mt-4">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-400 flex items-center justify-center text-white font-semibold text-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-xs text-slate-400">Admin</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-xs text-slate-400 hover:text-red-500" title="Logout">⏻</button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Overlay mobile --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
             class="fixed inset-0 bg-black/30 z-30 lg:hidden"></div>

        {{-- Main content --}}
        <div class="flex-1 min-w-0">
            {{-- Topbar --}}
            <header class="sticky top-0 z-20 bg-white/60 backdrop-blur-xl border-b border-white/50 px-4 sm:px-6 py-4 flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-600">
                    ☰
                </button>
                <div class="flex-1">
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800">@yield('title', 'Dashboard')</h1>
                </div>
                <div class="hidden sm:flex items-center bg-white/80 rounded-xl px-3 py-2 w-56 lg:w-72">
                    <span class="text-slate-400 mr-2">🔍</span>
                    <input type="text" placeholder="Cari sesuatu..." class="bg-transparent outline-none text-sm w-full">
                </div>
                <button class="relative text-slate-500">
                    🔔
                </button>
            </header>

            <main class="p-4 sm:p-6">
                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>
