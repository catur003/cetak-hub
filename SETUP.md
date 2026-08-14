# Setup CetakPro (Laravel)

## 1. Buat base project (jalankan di Termux/environment yang ada internet)

```bash
composer create-project laravel/laravel cetak-pro
cd cetak-pro
composer require laravel/breeze --dev
php artisan breeze:install blade
composer require spatie/laravel-permission
composer require cloudinary-labs/cloudinary-laravel
composer require midtrans/midtrans-php
```

## 2. Copy file dari folder in

Timpa/gabungkan folder berikut ke project barumu (semua file di sini adalah TAMBAHAN/OVERRIDE di atas base Laravel + Breeze):
- `database/migrations/*` → `database/migrations/`
- `app/Models/*` → `app/Models/`
- `app/Http/Controllers/*` → `app/Http/Controllers/`
- `resources/views/*` → `resources/views/`
- `routes/web.php` → `routes/web.php` (ganti isi)
- `config/*` → `config/`

## 3. Setup .env

Copy `.env.example` di sini, isi:
- Kredensial MySQL (Railway biasanya auto-provide `DATABASE_URL` atau var terpisah)
- `CLOUDINARY_URL` (dari dashboard Cloudinary)
- `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION=false`

## 4. Migrate & Seed

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
php artisan db:seed --class=RoleAndAdminSeeder
```

## 5. Jalankan

```bash
php artisan serve
```

Login admin default: `admin@cetakpro.test` / `password` (WAJIB ganti setelah login pertama — ada di seeder, lihat `database/seeders/RoleAndAdminSeeder.php`)
