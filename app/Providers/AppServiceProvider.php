<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // FIX (6 Agustus 2026, bug nyata: CSS/JS gak keload - Mixed Content).
        // Coolify (Traefik) terminate SSL di depan, forward ke container
        // pakai http:// biasa - Laravel gak selalu tau request ASLINYA
        // https, jadi asset()/Vite generate link http:// walau halaman
        // diakses via https. Browser blokir diam-diam (Mixed Content),
        // gak ada error jelas - cuma CSS/JS gak muncul.
        //
        // Paksa APP_ENV=production SELALU generate URL https - gak
        // gantung ke deteksi header proxy yang kadang gak konsisten.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
