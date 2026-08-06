@extends('layouts.admin')

@section('title', 'Pembayaran')

@section('content')
<div class="flex flex-wrap items-center gap-2 mb-6">
    @php $tabs = ['menunggu_verifikasi' => 'Menunggu Verifikasi', 'paid' => 'Lunas', 'failed' => 'Gagal/Ditolak', 'semua' => 'Semua']; @endphp
    @foreach ($tabs as $key => $label)
        <a href="{{ route('admin.payments.index', ['status' => $key]) }}"
           class="px-3 py-1.5 rounded-full text-xs font-medium {{ $status === $key ? 'bg-indigo-600 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-x-auto">
    <table class="w-full text-sm min-w-[750px]">
        <thead>
            <tr class="text-left text-slate-400 border-b border-slate-100">
                <th class="px-5 py-3 font-medium">No. Order</th>
                <th class="px-5 py-3 font-medium">Customer</th>
                <th class="px-5 py-3 font-medium">Metode</th>
                <th class="px-5 py-3 font-medium">Jumlah</th>
                <th class="px-5 py-3 font-medium">Bukti</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payments as $payment)
                <tr class="border-b border-slate-50">
                    <td class="px-5 py-3 font-medium text-slate-700">
                        <a href="{{ route('admin.orders.show', $payment->order) }}" class="hover:text-indigo-600">{{ $payment->order->order_number ?? '-' }}</a>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $payment->order->user->name ?? '-' }}</td>
                    <td class="px-5 py-3 text-slate-600 capitalize">{{ str_replace('_', ' ', $payment->method) }}</td>
                    <td class="px-5 py-3 text-slate-600">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                    <td class="px-5 py-3">
                        @if ($payment->proof_url)
                            <a href="{{ $payment->proof_url }}" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-700 font-medium text-xs">Lihat Bukti</a>
                        @else
                            <span class="text-slate-300 text-xs">-</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2.5 py-1 rounded-full text-xs capitalize
                            {{ $payment->status === 'paid' ? 'bg-emerald-50 text-emerald-600' : ($payment->status === 'failed' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600') }}">
                            {{ str_replace('_', ' ', $payment->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        @if ($payment->method === 'manual_transfer' && $payment->status === 'menunggu_verifikasi')
                            <div class="flex justify-end gap-2">
                                <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" onsubmit="return confirm('Verifikasi pembayaran ini? Order akan otomatis pindah ke status Dibayar.')">
                                    @csrf
                                    <button type="submit" class="text-emerald-600 hover:text-emerald-700 font-medium text-xs">Verifikasi</button>
                                </form>
                                <button type="button" onclick="document.getElementById('reject-{{ $payment->id }}').classList.toggle('hidden')" class="text-red-600 hover:text-red-700 font-medium text-xs">Tolak</button>
                            </div>
                            <div id="reject-{{ $payment->id }}" class="hidden mt-2 text-left">
                                <form method="POST" action="{{ route('admin.payments.reject', $payment) }}">
                                    @csrf
                                    <input type="text" name="reason" placeholder="Alasan penolakan" required
                                           class="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs mb-1.5">
                                    <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700">Kirim Penolakan</button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-8 text-center text-slate-400">Belum ada pembayaran.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $payments->links() }}</div>
@endsection
