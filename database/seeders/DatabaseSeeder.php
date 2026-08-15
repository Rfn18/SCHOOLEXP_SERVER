<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Jalankan RoleSeeder dulu (karena UserSeeder butuh role_id)
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            EventCategorySeeder::class,
            DocCategorySeeder::class,
            EventSeeder::class,
        ]);
    }
}