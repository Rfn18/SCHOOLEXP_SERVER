<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('doc_categories')->insert([
            [
                'id' => 1,
                'name' => 'Apel Pembukaan',
                'description' => 'apel-pembukaan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Apel Penutupan',
                'description' => 'apel-penutupan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}