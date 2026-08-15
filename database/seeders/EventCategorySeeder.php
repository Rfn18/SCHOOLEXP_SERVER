<?php

namespace Database\Seeders;

use App\Models\EventCategories;
use App\Models\EventCategory;
use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Pendidikan',
                'description' => 'Kegiatan yang berkaitan dengan pendidikan dan pembelajaran.',
            ],
            [
                'name' => 'Kegiatan Sekolah',
                'description' => 'Kegiatan umum yang diselenggarakan oleh sekolah.',
            ],
            [
                'name' => 'Lomba',
                'description' => 'Kompetisi dan perlombaan antar siswa.',
            ],
            [
                'name' => 'Keagamaan',
                'description' => 'Kegiatan keagamaan dan pembinaan spiritual.',
            ],
            [
                'name' => 'Teknologi',
                'description' => 'Kegiatan teknologi, komputer, programming, dan AI.',
            ],
            [
                'name' => 'Seni',
                'description' => 'Kegiatan seni dan kreativitas siswa.',
            ],
        ];

        foreach ($categories as $category) {
            EventCategories::updateOrCreate(
                [
                    'name' => $category['name'],
                ],
                [
                    'description' => $category['description'],
                ]
            );
        }

        $this->command->info('Event categories berhasil di-seed.');
    }
}