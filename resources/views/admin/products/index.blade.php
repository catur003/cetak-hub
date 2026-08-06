@extends('layouts.admin')

@section('title', 'Produk')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-slate-500">Kelola produk & varian.</p>
    <a href="{{ route('admin.products.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg">
        Tambah Produk
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-x-auto">
    <table class="w-full text-sm min-w-[700px]">
        <thead>
            <tr class="text-left text-slate-400 border-b border-slate-100">
                <th class="px-5 py-3 font-medium">Produk</th>
                <th class="px-5 py-3 font-medium">Kategori</th>
                <th class="px-5 py-3 font-medium">Harga Dasar</th>
                <th class="px-5 py-3 font-medium">Varian</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr class="border-b border-slate-50">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            @if ($product->thumbnail_url)
                                <img src="{{ $product->thumbnail_url }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200" alt="">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-slate-100"></div>
                            @endif
                            <span class="font-medium text-slate-700">{{ $product->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $product->category->name ?? '-' }}</td>
                    <td class="px-5 py-3 text-slate-600">Rp {{ number_format($product->base_price, 0, ',', '.') }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $product->variants_count }}</td>
                    <td class="px-5 py-3">
                        @if ($product->is_active)
                            <span class="px-2.5 py-1 rounded-full text-xs bg-emerald-50 text-emerald-600">Aktif</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs bg-slate-100 text-slate-500">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right space-x-3">
                        <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">Edit</a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline"
                              onsubmit="return confirm('Yakin hapus produk {{ $product->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-slate-400">Belum ada produk.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $products->links() }}</div>
@endsection
