@extends('layouts.admin')

@section('title', 'Kategori')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-slate-500">Kelola kategori produk.</p>
    <a href="{{ route('admin.categories.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg">
        Tambah Kategori
    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-x-auto">
    <table class="w-full text-sm min-w-[600px]">
        <thead>
            <tr class="text-left text-slate-400 border-b border-slate-100">
                <th class="px-5 py-3 font-medium">Nama</th>
                <th class="px-5 py-3 font-medium">Slug</th>
                <th class="px-5 py-3 font-medium">Jumlah Produk</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                <tr class="border-b border-slate-50">
                    <td class="px-5 py-3 font-medium text-slate-700">{{ $category->name }}</td>
                    <td class="px-5 py-3 text-slate-500 font-mono text-xs">{{ $category->slug }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $category->products_count }}</td>
                    <td class="px-5 py-3">
                        @if ($category->is_active)
                            <span class="px-2.5 py-1 rounded-full text-xs bg-emerald-50 text-emerald-600">Aktif</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs bg-slate-100 text-slate-500">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right space-x-3">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">Edit</a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline"
                              onsubmit="return confirm('Yakin hapus kategori {{ $category->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada kategori.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $categories->links() }}</div>
@endsection
