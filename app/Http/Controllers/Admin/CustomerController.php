<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        // withCount orders - jangan load semua order tiap customer ke memori
        // cuma buat nampilin jumlahnya doang di list.
        $customers = User::role('customer')
            ->withCount('orders')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.customers.index', compact('customers'));
    }

    public function toggleActive(User $customer): RedirectResponse
    {
        // Guard: pastiin yang di-toggle beneran role customer, bukan admin
        // lain yang applicationUuid-nya "kebetulan" ke-pass ke sini lewat
        // manipulasi URL manual (route model binding gak cek role otomatis).
        if (! $customer->hasRole('customer')) {
            abort(404);
        }

        $customer->update(['is_active' => ! $customer->is_active]);

        return back()->with('success', $customer->is_active ? 'Customer diaktifkan.' : 'Customer dinonaktifkan.');
    }
}
