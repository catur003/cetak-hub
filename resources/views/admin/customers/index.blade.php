@extends('layouts.admin')

@section('title', 'Customer')

@section('content')
<p class="text-slate-500 mb-6">Kelola akun customer.</p>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-x-auto">
    <table class="w-full text-sm min-w-[650px]">
        <thead>
            <tr class="text-left text-slate-400 border-b border-slate-100">
                <th class="px-5 py-3 font-medium">Nama</th>
                <th class="px-5 py-3 font-medium">Email</th>
                <th class="px-5 py-3 font-medium">Telepon</th>
                <th class="px-5 py-3 font-medium">Jumlah Order</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customers as $customer)
                <tr class="border-b border-slate-50">
                    <td class="px-5 py-3 font-medium text-slate-700">{{ $customer->name }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $customer->email }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $customer->phone ?? '-' }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $customer->orders_count }}</td>
                    <td class="px-5 py-3">
                        @if ($customer->is_active)
                            <span class="px-2.5 py-1 rounded-full text-xs bg-emerald-50 text-emerald-600">Aktif</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs bg-slate-100 text-slate-500">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        <form method="POST" action="{{ route('admin.customers.toggle-active', $customer) }}" class="inline"
                              onsubmit="return confirm('{{ $customer->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $customer->name }}?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="font-medium text-xs {{ $customer->is_active ? 'text-red-600 hover:text-red-700' : 'text-emerald-600 hover:text-emerald-700' }}">
                                {{ $customer->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-slate-400">Belum ada customer.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $customers->links() }}</div>
@endsection
