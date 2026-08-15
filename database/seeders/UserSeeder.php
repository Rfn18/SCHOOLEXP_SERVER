<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Roles;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil role IDs (asumsikan sudah ada dari RoleSeeder)
        $adminRole = Roles::where('guard_name', 'admin')->first();
        $userRole = Roles::where('guard_name', 'user')->first();
        $organizerRole = Roles::where('guard_name', 'event-organizer')->first();

        $users = [
            [
                'name' => 'Admin User',
                'email' => 'rinofaster89@gmail.com',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id ?? 1,
                'profile_picture' => null,
            ],
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password123'),
                'role_id' => $userRole->id ?? 2,
                'profile_picture' => null,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password123'),
                'role_id' => $userRole->id ?? 2,
                'profile_picture' => null,
            ],
            [
                'name' => 'Event Organizer 1',
                'email' => 'organizer1@example.com',
                'password' => Hash::make('password123'),
                'role_id' => $organizerRole->id ?? 3,
                'profile_picture' => null,
            ],
            [
                'name' => 'Event Organizer 2',
                'email' => 'organizer2@example.com',
                'password' => Hash::make('password123'),
                'role_id' => $organizerRole->id ?? 3,
                'profile_picture' => null,
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}