@extends('layouts.storefront')

@section('title', 'Produk - CetakPro')

@section('content')
<div class="flex flex-wrap items-center gap-2 mb-6">
    <a href="{{ route('storefront.products.index') }}"
       class="px-3 py-1.5 rounded-full text-xs font-medium {{ ! $selectedCategory ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
        Semua
    </a>
    @foreach ($categories as $category)
        <a href="{{ route('storefront.products.index', ['category' => $category->slug]) }}"
           class="px-3 py-1.5 rounded-full text-xs font-medium {{ $selectedCategory === $category->slug ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
            {{ $category->name }}
        </a>
    @endforeach
</div>

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
    @forelse ($products as $product)
        <a href="{{ route('storefront.products.show', $product) }}" class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-md transition">
            @if ($product->thumbnail_url)
                <img src="{{ $product->thumbnail_url }}" class="w-full aspect-square object-cover" alt="{{ $product->name }}">
            @else
                <div class="w-full aspect-square bg-slate-100"></div>
            @endif
            <div class="p-3">
                <p class="text-xs text-slate-400 mb-0.5">{{ $product->category->name ?? '' }}</p>
                <p class="text-sm font-semibold text-slate-800 truncate">{{ $product->name }}</p>
                <p class="text-sm font-bold text-indigo-600 mt-1">
                    Rp {{ number_format($product->min_price ?? $product->base_price, 0, ',', '.') }}
                </p>
            </div>
        </a>
    @empty
        <p class="col-span-full text-center text-slate-400 py-12">Belum ada produk.</p>
    @endforelse
</div>

<div class="mt-6">{{ $products->links() }}</div>
@endsection
