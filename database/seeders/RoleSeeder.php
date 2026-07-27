<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Roles;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'guard_name' => 'admin'],
            ['name' => 'User', 'guard_name' => 'user'],
            ['name' => 'Event Organizer', 'guard_name' => 'event-organizer'],
        ];

        foreach ($roles as $role) {
            Roles::firstOrCreate(
                ['guard_name' => $role['guard_name']],
                ['name' => $role['name']]
            );
        }
    }
}