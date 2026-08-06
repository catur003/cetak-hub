@extends('layouts.storefront')

@section('title', 'Pembayaran - CetakPro')

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-xl font-bold text-slate-800 mb-1">Pembayaran</h1>
    <p class="text-slate-500 text-sm mb-6">Pesanan {{ $order->order_number }} - Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>

    @if ($order->payment && $order->payment->method === 'midtrans')
        <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
            @if ($snapToken)
                <p class="text-slate-600 text-sm mb-4">Klik tombol di bawah untuk lanjut ke halaman pembayaran.</p>
                <button id="pay-button" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-3 rounded-lg">
                    Bayar Sekarang
                </button>
            @elseif ($order->payment->status === 'paid')
                <p class="text-emerald-600 font-medium">Pembayaran sudah lunas.</p>
                <a href="{{ route('checkout.success', $order) }}" class="inline-block mt-3 text-indigo-600 font-medium hover:text-indigo-700">Lihat Detail Pesanan &rarr;</a>
            @else
                <p class="text-slate-500 text-sm">Status pembayaran: <span class="font-medium capitalize">{{ str_replace('_', ' ', $order->payment->status) }}</span></p>
                <p class="text-xs text-slate-400 mt-2">Kalau kamu sudah bayar tapi status belum berubah, tunggu beberapa saat lalu refresh halaman ini.</p>
            @endif
        </div>

        @if ($snapToken)
            <script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
                    data-client-key="{{ config('midtrans.client_key') }}"></script>
            <script>
                document.getElementById('pay-button').addEventListener('click', function () {
                    snap.pay('{{ $snapToken }}', {
                        onSuccess: function () { window.location.href = '{{ route('checkout.success', $order) }}'; },
                        onPending: function () { window.location.href = '{{ route('checkout.success', $order) }}'; },
                        onError: function () { alert('Pembayaran gagal, silakan coba lagi.'); },
                        onClose: function () { /* biarin, customer bisa buka lagi halaman ini kapan aja buat generate ulang */ }
                    });
                });
            </script>
        @endif
    @elseif ($order->payment && $order->payment->method === 'manual_transfer')
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            @if ($order->payment->status === 'pending')
                <div class="bg-slate-50 rounded-lg p-4 mb-5 text-sm text-slate-600">
                    <p class="font-medium text-slate-800 mb-1">Instruksi Transfer</p>
                    <p>Transfer ke rekening toko sesuai nominal di atas, lalu upload bukti transfernya di bawah.</p>
                    <p class="text-xs text-slate-400 mt-2">(Nomor rekening toko: isi manual di sini sesuai rekening yang dipakai)</p>
                </div>

                <form method="POST" action="{{ route('checkout.upload-proof', $order) }}" enctype="multipart/form-data">
                    @csrf
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Pengirim / Rekening Asal</label>
                    <input type="text" name="bank_sender" required
                           class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm mb-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('bank_sender') <p class="text-xs text-red-600 -mt-3 mb-3">{{ $message }}</p> @enderror

                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Bukti Transfer</label>
                    <input type="file" name="proof" accept="image/*" required
                           class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:text-sm file:font-medium mb-1">
                    <p class="text-xs text-slate-400 mb-4">Maks 2MB.</p>
                    @error('proof') <p class="text-xs text-red-600 -mt-3 mb-3">{{ $message }}</p> @enderror

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-3 rounded-lg">
                        Upload Bukti Transfer
                    </button>
                </form>
            @elseif ($order->payment->status === 'menunggu_verifikasi')
                <p class="text-amber-600 font-medium">Bukti transfer sudah diupload, menunggu verifikasi admin.</p>
            @elseif ($order->payment->status === 'paid')
                <p class="text-emerald-600 font-medium">Pembayaran sudah diverifikasi.</p>
            @else
                <p class="text-red-600 font-medium">Pembayaran ditolak. Hubungi admin untuk info lebih lanjut.</p>
            @endif
        </div>
    @endif
</div>
@endsection
