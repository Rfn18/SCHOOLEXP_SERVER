<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategories;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        if (!$user) {
            $this->command->error('User belum tersedia. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        $categories = EventCategories::pluck('id', 'name');

        if ($categories->isEmpty()) {
            $this->command->error(
                'Event category belum tersedia. Jalankan EventCategorySeeder terlebih dahulu.'
            );

            return;
        }

        $events = [
            [
                'title' => 'MPLS SMK Bhakti Wiyata 2026',
                'description' => 'Masa Pengenalan Lingkungan Sekolah bagi peserta didik baru SMK Bhakti Wiyata.',
                'location' => 'Aula SMK Bhakti Wiyata',
                'cover_image' => 'events/mpls-2026.jpg',
                'start_date' => '2026-07-13',
                'end_date' => '2026-07-16',
                'start_time' => '07:00',
                'end_time' => '14:00',
                'link' => null,
                'status' => 'completed',
                'is_repeat' => false,
                'category' => 'Pendidikan',
            ],
            [
                'title' => 'Upacara HUT Kemerdekaan Indonesia',
                'description' => 'Upacara memperingati Hari Ulang Tahun Kemerdekaan Republik Indonesia.',
                'location' => 'Lapangan Utama Sekolah',
                'cover_image' => 'events/hut-ri-2026.jpg',
                'start_date' => '2026-08-17',
                'end_date' => '2026-08-17',
                'start_time' => '07:00',
                'end_time' => '09:00',
                'link' => null,
                'status' => 'upcoming',
                'is_repeat' => true,
                'category' => 'Kegiatan Sekolah',
            ],
            [
                'title' => 'Lomba Kemerdekaan 2026',
                'description' => 'Serangkaian perlombaan antar kelas dalam rangka memperingati Hari Kemerdekaan Indonesia.',
                'location' => 'Lapangan Sekolah',
                'cover_image' => 'events/lomba-kemerdekaan-2026.jpg',
                'start_date' => '2026-08-18',
                'end_date' => '2026-08-19',
                'start_time' => '08:00',
                'end_time' => '15:00',
                'link' => null,
                'status' => 'upcoming',
                'is_repeat' => true,
                'category' => 'Lomba',
            ],
            [
                'title' => 'Kajian Islami dan Doa Bersama',
                'description' => 'Kegiatan kajian Islami dan doa bersama yang diikuti oleh seluruh warga sekolah.',
                'location' => 'Masjid Sekolah',
                'cover_image' => 'events/kajian-islami.jpg',
                'start_date' => '2026-08-21',
                'end_date' => '2026-08-21',
                'start_time' => '08:00',
                'end_time' => '11:00',
                'link' => null,
                'status' => 'upcoming',
                'is_repeat' => false,
                'category' => 'Keagamaan',
            ],
            [
                'title' => 'Class Meeting Semester Ganjil',
                'description' => 'Kegiatan class meeting yang diselenggarakan setelah pelaksanaan ujian semester.',
                'location' => 'Area Sekolah',
                'cover_image' => 'events/class-meeting.jpg',
                'start_date' => '2026-12-14',
                'end_date' => '2026-12-18',
                'start_time' => '07:30',
                'end_time' => '15:00',
                'link' => null,
                'status' => 'upcoming',
                'is_repeat' => true,
                'category' => 'Kegiatan Sekolah',
            ],
            [
                'title' => 'Seminar Teknologi dan AI',
                'description' => 'Seminar mengenai perkembangan teknologi, artificial intelligence, dan peluang karier di industri digital.',
                'location' => 'Auditorium Sekolah',
                'cover_image' => 'events/seminar-ai.jpg',
                'start_date' => '2026-09-12',
                'end_date' => '2026-09-12',
                'start_time' => '09:00',
                'end_time' => '13:00',
                'link' => 'https://example.com/seminar-ai',
                'status' => 'upcoming',
                'is_repeat' => false,
                'category' => 'Teknologi',
            ],
            [
                'title' => 'Pentas Seni dan Kreativitas Siswa',
                'description' => 'Pentas seni yang menampilkan berbagai karya dan kreativitas siswa.',
                'location' => 'Lapangan Utama Sekolah',
                'cover_image' => 'events/pentas-seni.jpg',
                'start_date' => '2026-10-24',
                'end_date' => '2026-10-24',
                'start_time' => '08:00',
                'end_time' => '16:00',
                'link' => null,
                'status' => 'upcoming',
                'is_repeat' => false,
                'category' => 'Seni',
            ],
            [
                'title' => 'Workshop Web Development',
                'description' => 'Workshop pengembangan website menggunakan teknologi modern untuk siswa bidang Rekayasa Perangkat Lunak.',
                'location' => 'Laboratorium Komputer',
                'cover_image' => 'events/workshop-web-development.jpg',
                'start_date' => '2026-09-26',
                'end_date' => '2026-09-27',
                'start_time' => '08:00',
                'end_time' => '15:00',
                'link' => null,
                'status' => 'upcoming',
                'is_repeat' => false,
                'category' => 'Teknologi',
            ],
        ];

        foreach ($events as $event) {
            $categoryName = $event['category'];

            unset($event['category']);

            $categoryId = $categories->get($categoryName);

            if (!$categoryId) {
                $this->command->warn(
                    "Kategori '{$categoryName}' tidak ditemukan. Event '{$event['title']}' dilewati."
                );

                continue;
            }

            Event::updateOrCreate(
                [
                    'slug' => Str::slug($event['title']),
                ],
                [
                    ...$event,
                    'user_id' => 1,
                    'event_category_id' => $categoryId,
                ]
            );
        }

        $this->command->info('Event berhasil di-seed.');
    }
}