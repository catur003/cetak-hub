@extends('layouts.admin')

@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 max-w-3xl"
     x-data="{
        name: {{ Js::from(old('name', $product->name)) }},
        slug: {{ Js::from(old('slug', $product->slug)) }},
        slugTouched: {{ $product->exists ? 'true' : 'false' }},
        variants: {{ Js::from(
            old('variants', $product->variants->map(fn ($v) => [
                'id' => $v->id, 'ukuran' => $v->ukuran, 'bahan' => $v->bahan,
                'price' => $v->price, 'min_order' => $v->min_order,
                'is_active' => $v->is_active, '_delete' => false,
            ])->values()->all())
        ) }},
        addVariant() {
            this.variants.push({ id: null, ukuran: '', bahan: '', price: '', min_order: 1, is_active: true, _delete: false });
        },
        removeVariant(index) {
            if (this.variants[index].id) {
                this.variants[index]._delete = true; // varian LAMA: tandai delete, jangan splice (biar tetep kekirim ke server)
            } else {
                this.variants.splice(index, 1); // varian baru (belum tersimpan): buang aja dari array
            }
        }
     }">
    <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Produk</label>
                <input type="text" name="name" x-model="name"
                       @input="if (!slugTouched) { slug = name.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''); }"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Slug</label>
                <input type="text" name="slug" x-model="slug" @input="slugTouched = true"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                @error('slug') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Kategori</label>
                <select name="category_id" class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Harga Dasar (Rp)</label>
                <input type="number" name="base_price" min="0" step="1" value="{{ old('base_price', $product->base_price) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                <p class="text-xs text-slate-400 mt-1">Harga "mulai dari" - harga real per varian diatur di bawah.</p>
                @error('base_price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi</label>
            <textarea name="description" rows="3"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $product->description) }}</textarea>
            @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Thumbnail</label>
            @if ($product->thumbnail_url)
                <img src="{{ $product->thumbnail_url }}" class="w-20 h-20 rounded-lg object-cover border border-slate-200 mb-2" alt="">
            @endif
            <input type="file" name="thumbnail" accept="image/*"
                   class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:text-sm file:font-medium">
            <p class="text-xs text-slate-400 mt-1">Maks 2MB. Kosongkan kalau gak mau ganti thumbnail.</p>
            @error('thumbnail') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Varian --}}
        <div class="mb-6 border border-slate-200 rounded-xl p-4">
            <div class="flex items-center justify-between mb-3">
                <label class="text-sm font-semibold text-slate-700">Varian Produk</label>
                <button type="button" @click="addVariant()" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                    + Tambah Varian
                </button>
            </div>
            @error('variants') <p class="text-xs text-red-600 mb-2">{{ $message }}</p> @enderror

            <template x-for="(variant, index) in variants" :key="index">
                <div x-show="!variant._delete" class="grid grid-cols-1 sm:grid-cols-12 gap-2 mb-2 items-center">
                    <input type="hidden" :name="`variants[${index}][id]`" x-model="variant.id">
                    <input type="hidden" :name="`variants[${index}][_delete]`" :value="variant._delete ? 1 : 0">
                    <input type="text" :name="`variants[${index}][ukuran]`" x-model="variant.ukuran" placeholder="Ukuran (A3, 60x160)"
                           class="sm:col-span-3 border border-slate-300 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <input type="text" :name="`variants[${index}][bahan]`" x-model="variant.bahan" placeholder="Bahan"
                           class="sm:col-span-3 border border-slate-300 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <input type="number" :name="`variants[${index}][price]`" x-model="variant.price" placeholder="Harga" min="0"
                           class="sm:col-span-2 border border-slate-300 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <input type="number" :name="`variants[${index}][min_order]`" x-model="variant.min_order" placeholder="Min. Order" min="1"
                           class="sm:col-span-2 border border-slate-300 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <label class="sm:col-span-1 flex items-center gap-1 text-xs text-slate-500">
                        <input type="checkbox" :name="`variants[${index}][is_active]`" value="1" x-model="variant.is_active" class="rounded border-slate-300 text-indigo-600">
                        Aktif
                    </label>
                    <button type="button" @click="removeVariant(index)" class="sm:col-span-1 text-red-500 hover:text-red-600 text-xs font-medium">Hapus</button>
                </div>
            </template>
            <p class="text-xs text-slate-400 mt-2">Minimal 1 varian aktif. Varian yang udah pernah dipesan gak bisa dihapus, cuma bisa dinonaktifkan.</p>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg">
                Simpan
            </button>
            <a href="{{ route('admin.products.index') }}" class="text-slate-600 text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-slate-50">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
