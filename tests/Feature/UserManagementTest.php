<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB; // TAMBAHKAN INI
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $regularUser;

    //    protected function setUp(): void
    // {
    //     parent::setUp();
        
    //     // Tambahkan 'guard_name' => 'web'
    //     DB::table('roles')->insert([
    //         ['id' => 1, 'name' => 'Admin', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    //         ['id' => 2, 'name' => 'User', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    //     ]);
        
    //     // Setup user untuk testing
    //     $this->admin = User::factory()->create(['role_id' => 1]);
    //     $this->regularUser = User::factory()->create(['role_id' => 2]);
    // }

    public function test_admin_can_view_user_details()
    {
        $response = $this->actingAs($this->admin, 'api')
                         ->getJson("/api/users/{$this->regularUser->id}");

        $response->assertStatus(200)
                 ->assertJson(['data' => ['id' => $this->regularUser->id]]);
    }

    public function test_admin_can_create_new_user()
    {
        $payload = [
            'name' => 'User Baru',
            'email' => 'baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => 2,
        ];

        $response = $this->actingAs($this->admin, 'api')
                         ->postJson('/api/users', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'baru@example.com']);
    }

    public function test_admin_can_update_user_without_changing_password()
    {
        $payload = [
            'name' => 'Nama Diubah',
        ];

        $response = $this->actingAs($this->admin, 'api')
                         ->putJson("/api/users/{$this->regularUser->id}", $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $this->regularUser->id, 'name' => 'Nama Diubah']);
    }

    public function test_admin_can_update_user_with_same_email()
    {
        $payload = [
            'name' => 'Nama Diubah Lagi',
            'email' => $this->regularUser->email, 
        ];

        $response = $this->actingAs($this->admin, 'api')
                         ->putJson("/api/users/{$this->regularUser->id}", $payload);

        $response->assertStatus(200); 
    }

    public function test_admin_can_delete_user()
    {
        $response = $this->actingAs($this->admin, 'api')
                         ->deleteJson("/api/users/{$this->regularUser->id}");

        $response->assertStatus(200); 
    }
}