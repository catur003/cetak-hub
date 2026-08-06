<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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
