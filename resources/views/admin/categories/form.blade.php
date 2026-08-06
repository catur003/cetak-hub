@extends('layouts.admin')

@section('title', $category->exists ? 'Edit Kategori' : 'Tambah Kategori')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 max-w-xl"
     x-data="{ name: '{{ old('name', $category->name) }}', slug: '{{ old('slug', $category->slug) }}', slugTouched: {{ $category->exists ? 'true' : 'false' }} }">
    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Kategori</label>
            <input type="text" name="name" x-model="name"
                   @input="if (!slugTouched) { slug = name.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''); }"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="Banner & Spanduk" required>
            @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Slug</label>
            <input type="text" name="slug" x-model="slug" @input="slugTouched = true"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="banner-spanduk" required>
            <p class="text-xs text-slate-400 mt-1">Otomatis ke-generate dari nama, bisa diedit manual.</p>
            @error('slug') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi</label>
            <textarea name="description" rows="3"
                      class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $category->description) }}</textarea>
            @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6 flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                   {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}
                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            <label for="is_active" class="text-sm text-slate-700">Aktif (tampil di storefront)</label>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg">
                Simpan
            </button>
            <a href="{{ route('admin.categories.index') }}" class="text-slate-600 text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-slate-50">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
