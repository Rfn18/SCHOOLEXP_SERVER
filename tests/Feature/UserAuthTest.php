<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserAuthTest extends TestCase
{
    use RefreshDatabase; 

    // protected function setUp(): void
    // {
    //     parent::setUp();
        
    //     // PERBAIKAN: Tambahkan 'guard_name' => 'web'
    //     DB::table('roles')->insert([
    //         ['id' => 1, 'name' => 'Admin', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    //         ['id' => 2, 'name' => 'User', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
    //     ]);
    // }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['token', 'user']]); 
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401); 
    }

    public function test_authenticated_user_can_get_their_own_data()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->getJson('/api/me'); 

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'id' => $user->id,
                         'email' => $user->email,
                     ]
                 ]);
    }

    public function test_unauthenticated_user_cannot_access_me_endpoint()
    {
        $response = $this->getJson('/api/me');
        $response->assertStatus(401);
    }
}