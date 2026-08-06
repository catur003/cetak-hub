<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\MidtransWebhookController;
use App\Http\Controllers\Storefront\ProductController as StorefrontProductController;
use Illuminate\Support\Facades\Route;

// ==== Storefront publik (gak butuh login) ====
Route::get('/', [StorefrontProductController::class, 'index'])->name('storefront.products.index');
Route::get('/produk/{product:slug}', [StorefrontProductController::class, 'show'])->name('storefront.products.show');

// FIX (6 Agustus 2026, bug nyata: "Route [dashboard] not defined"): Breeze
// bawaan (AuthenticatedSessionController@store, TIDAK disentuh file Phase 1)
// hardcode redirect abis login ke route('dashboard'). Sekarang cek role -
// admin ke admin.dashboard, customer ke storefront (belanja).
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user && $user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('storefront.products.index');
})->middleware('auth')->name('dashboard');

// ==== Area Admin (proteksi: login + role admin + akun aktif) ====
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('categories/generate-slug', [CategoryController::class, 'generateSlug'])->name('categories.generate-slug');
        Route::resource('categories', CategoryController::class)->except(['show']);

        Route::get('products/generate-slug', [ProductController::class, 'generateSlug'])->name('products.generate-slug');
        Route::resource('products', ProductController::class)->except(['show']);

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');

        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
        Route::post('payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::patch('customers/{customer}/toggle-active', [CustomerController::class, 'toggleActive'])->name('customers.toggle-active');
    });

// ==== Area Customer (login biasa - keranjang, checkout, profil) ====
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/keranjang', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/keranjang/{variantId}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/keranjang/{variantId}', [CartController::class, 'destroy'])->name('cart.destroy');

    // throttle:10,1 - checkout bikin Order+OrderItem ke DB, batasi 10x
    // percobaan per menit per user biar gak disalahgunain buat spam order
    // (mis. bot/script iseng), gak ngeganggu customer normal yang checkout
    // wajar cuma sekali-dua kali.
    Route::middleware('throttle:10,1')->group(function () {
        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    });
    Route::get('/checkout/{order}/bayar', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/checkout/{order}/upload-bukti', [CheckoutController::class, 'uploadProof'])->name('checkout.upload-proof');
    Route::get('/checkout/{order}/selesai', [CheckoutController::class, 'success'])->name('checkout.success');
});

// ==== Webhook Midtrans (publik, TANPA middleware auth - proteksinya
// signature_key verification di controller, BUKAN session/login) ====
// throttle:30,1 - proteksi dasar dari flood, longgar karena Midtrans bisa
// legitimately kirim beberapa notifikasi berturutan (retry policy mereka).
Route::post('/webhooks/midtrans', [MidtransWebhookController::class, 'handle'])
    ->middleware('throttle:30,1')
    ->name('webhooks.midtrans');

require __DIR__.'/auth.php';
