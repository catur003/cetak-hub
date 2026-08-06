<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Dipasang di grup route /admin. Selain cek role, cek juga is_active
     * biar akun admin yang dinonaktifkan langsung ke-block walau sesi login masih ada.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active || ! $user->hasRole('admin')) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
