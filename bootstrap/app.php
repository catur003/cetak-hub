<?php
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);

        // Webhook Midtrans - Midtrans gak ngirim CSRF token Laravel, endpoint
        // ini WAJIB dikecualikan atau semua notifikasi bakal ke-reject 419.
        // Proteksinya BUKAN CSRF, tapi signature_key verification di
        // MidtransWebhookController - lihat komentar di sana.
        $middleware->validateCsrfTokens(except: [
            'webhooks/midtrans',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
