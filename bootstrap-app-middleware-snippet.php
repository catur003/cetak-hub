<?php
/**
 * TEMPEL potongan ini ke bootstrap/app.php project barumu,
 * di dalam ->withMiddleware(function (Middleware $middleware) { ... })
 *
 * Laravel 11 tidak lagi punya app/Http/Kernel.php, jadi alias middleware
 * didaftarkan di sini.
 */

use App\Http\Middleware\EnsureUserIsAdmin;

// di dalam withMiddleware():
$middleware->alias([
    'admin' => EnsureUserIsAdmin::class,
]);
