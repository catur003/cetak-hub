<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// FIX (6 Agustus 2026, bug nyata: "Route [dashboard] not defined"): Breeze
// bawaan (AuthenticatedSessionController@store, TIDAK disentuh file Phase 1)
// hardcode redirect abis login ke route('dashboard') - tapi CetakPro cuma
// punya 'admin.dashboard' (di dalam prefix admin). Alias sederhana ini
// nutup gap-nya. NANTI kalau customer dashboard udah dibangun (Phase 2),
// route ini WAJIB dipecah jadi cek role dulu (admin -> admin.dashboard,
// customer -> customer dashboard) - sekarang semua user login diarahkan
// ke admin dashboard karena baru itu doang yang ada.
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware('auth')->name('dashboard');

// ==== Area Admin (proteksi: login + role admin + akun aktif) ====
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Phase berikutnya akan nambah di sini:
        // Route::resource('categories', CategoryController::class);
        // Route::resource('products', ProductController::class);
        // Route::resource('orders', OrderController::class)->only(['index','show']);
        // Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        // Route::post('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    });

// ==== Area Customer (login biasa) ====
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Redirect customer yang login ke halaman yang sesuai (dicek di controller/gate nanti)
});

require __DIR__.'/auth.php';
