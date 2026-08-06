<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Pintu masuk standar `php artisan db:seed` - KONVENSI Laravel, sama di
     * SEMUA project (bukan cuma CetakPro). Command Post-deployment di
     * Coolify jadi konsisten 1 baris yang sama persis buat project apapun:
     *
     *   php artisan migrate --force && php artisan db:seed --force
     *
     * Yang beda tiap project cuma daftar $this->call(...) di bawah ini -
     * bukan command-nya.
     */
    public function run(): void
    {
        $this->call(RoleAndAdminSeeder::class);
    }
}
