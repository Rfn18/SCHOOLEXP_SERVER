<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('event_categories')->insert([
            [
                'id' => 1,
                'name' => 'Acara Sekolah',
                'description' => 'acara-sekolah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Olahraga',
                'description' => 'olahraga',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}