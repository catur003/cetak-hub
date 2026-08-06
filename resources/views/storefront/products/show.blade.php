@extends('layouts.storefront')

@section('title', $product->name . ' - CetakPro')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <div>
        @if ($product->thumbnail_url)
            <img src="{{ $product->thumbnail_url }}" class="w-full rounded-2xl border border-slate-200 aspect-square object-cover" alt="{{ $product->name }}">
        @else
            <div class="w-full rounded-2xl bg-slate-100 aspect-square"></div>
        @endif
    </div>

    <div x-data="{
            variantId: {{ $product->variants->first()->id }},
            variants: {{ Js::from($product->variants->map(fn ($v) => ['id' => $v->id, 'label' => trim(($v->ukuran ?? '').' '.($v->bahan ?? '')), 'price' => (float) $v->price, 'min_order' => $v->min_order])) }},
            qty: {{ $product->variants->first()->min_order }},
            get selected() { return this.variants.find(v => v.id === this.variantId); },
            get subtotal() { return this.selected.price * this.qty; }
         }">
        <p class="text-sm text-indigo-600 font-medium mb-1">{{ $product->category->name ?? '' }}</p>
        <h1 class="text-2xl font-bold text-slate-800 mb-3">{{ $product->name }}</h1>
        <p class="text-2xl font-bold text-slate-800 mb-4" x-text="'Rp ' + selected.price.toLocaleString('id-ID')"></p>

        @if ($product->description)
            <p class="text-slate-600 text-sm mb-6">{{ $product->description }}</p>
        @endif

        <form method="POST" action="{{ route('cart.store') }}">
            @csrf
            <input type="hidden" name="variant_id" x-model="variantId">

            <label class="block text-sm font-medium text-slate-700 mb-2">Varian</label>
            <div class="flex flex-wrap gap-2 mb-4">
                <template x-for="v in variants" :key="v.id">
                    <button type="button" @click="variantId = v.id; qty = v.min_order"
                            :class="variantId === v.id ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-200'"
                            class="px-3 py-2 rounded-lg text-sm font-medium border" x-text="v.label || 'Standar'"></button>
                </template>
            </div>

            <label class="block text-sm font-medium text-slate-700 mb-2">Jumlah (min. <span x-text="selected.min_order"></span>)</label>
            <input type="number" name="qty" x-model.number="qty" :min="selected.min_order"
                   class="w-32 border border-slate-300 rounded-lg px-3 py-2.5 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">

            <label class="block text-sm font-medium text-slate-700 mb-2">Catatan Desain (opsional)</label>
            <textarea name="notes" rows="2" placeholder="Detail desain, warna, dll"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>

            <p class="text-sm text-slate-500 mb-4">Subtotal: <span class="font-bold text-slate-800" x-text="'Rp ' + subtotal.toLocaleString('id-ID')"></span></p>

            @auth
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-3 rounded-lg">
                    Tambah ke Keranjang
                </button>
            @else
                <a href="{{ route('login') }}" class="block text-center w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-3 rounded-lg">
                    Masuk untuk Memesan
                </a>
            @endauth
        </form>
    </div>
</div>
@endsection
