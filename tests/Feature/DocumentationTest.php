<?php

namespace Tests\Feature;

use App\Models\Documentation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentationTest extends TestCase
{
    use RefreshDatabase;

      protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('cloudinary');

        // 1. Insert Role
        DB::table('roles')->updateOrInsert(['id' => 1], [
            'name' => 'Admin', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()
        ]);

        // 2. Insert User
        DB::table('users')->updateOrInsert(['id' => 99], [
            'name' => 'Dummy User', 'email' => 'dummy@test.com', 'password' => bcrypt('password'),
            'role_id' => 1, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()
        ]);

        DB::table('doc_categories')->updateOrInsert(['id' => 1], [
            'name' => 'Dummy Category', 
            'description' => 'dummy-category', 
            'created_at' => now(), 
            'updated_at' => now()
        ]);

        DB::table('event_categories')->updateOrInsert(['id' => 1], [
            'name' => 'Dummy Category', 
            'description' => 'dummy-category', 
            'created_at' => now(), 
            'updated_at' => now()
        ]);

        // 3. Insert Event (PERBAIKAN: Tambahkan semua kolom NOT NULL dari migration events)
        DB::table('events')->updateOrInsert(['id' => 99], [
            'title' => 'Dummy Event', 
            'slug' => 'dummy-event', 
            'description' => 'This is a dummy description for testing.',
            'location' => 'Jakarta',
            'start_date' => now(),
            'end_date' => now(),
            'start_time' => now(),
            'end_time' => now(),
            'cover_image' => 'dummy-thumb.jpg',
            'event_category_id' => 1,
            'status' => 'upcoming',
            'user_id' => 99, 
            'created_at' => now(), 
            'updated_at' => now()
        ]);

        // 4. Insert DocGallery
        DB::table('doc_galleries')->updateOrInsert(['id' => 99], [
            'doc_category_id' => 1,
            'event_id' => 99, 
            'created_at' => now(), 
            'updated_at' => now()
        ]);
    }

    public function test_can_get_all_documentations()
    {
        Documentation::factory()->count(3)->create();

        $response = $this->getJson('/api/documentations');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success', 'message', 
                     'data' => ['*' => ['id', 'file_path', 'alt_text', 'type', 'gallery_id', 'soft_order', 'url']]
                 ]);
    }

    public function test_can_filter_documentations_by_gallery_id()
    {
        Documentation::factory()->count(2)->create(['gallery_id' => 99]);
        
        DB::table('doc_galleries')->insert(['id' => 100, 'doc_category_id' => 1, 'event_id' => 99, 'created_at' => now(), 'updated_at' => now()]);
        Documentation::factory()->count(3)->create(['gallery_id' => 100]);

        $response = $this->getJson('/api/documentations?gallery_id=99');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        $this->assertCount(2, $data); 
    }

    public function test_can_create_documentation()
    {
        $file = UploadedFile::fake()->image('document.jpg');

        $payload = [
            'image' => $file,
            'alt_text' => 'Test Alt Text',
            'type' => 'medium', // <-- UBAH MENJADI 'small', 'medium', ATAU 'large'
            'gallery_id' => 99,
            'soft_order' => 0,
        ];

        $response = $this->postJson('/api/documentations', $payload);

        $response->assertStatus(200) 
                 ->assertJson(['success' => true]);
                 
        $this->assertDatabaseHas('documentations', [
            'alt_text' => 'Test Alt Text',
            'gallery_id' => 99,
        ]);
    }

    public function test_can_show_documentation()
    {
        $doc = Documentation::factory()->create();

        $response = $this->getJson("/api/documentations/{$doc->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $doc->id]);
    }

    public function test_can_update_documentation_without_image()
    {
        $doc = Documentation::factory()->create();

        $payload = [
            'alt_text' => 'Updated Alt',
            'type' => 'medium', // <-- UBAH
            'gallery_id' => 99,
            'soft_order' => 5,
        ];

        $response = $this->putJson("/api/documentations/{$doc->id}", $payload);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
                 
        $this->assertDatabaseHas('documentations', [
            'id' => $doc->id,
            'alt_text' => 'Updated Alt',
            'soft_order' => 5,
        ]);
    }

    public function test_update_documentation_with_image_will_fail_due_to_undefined_property()
    {
        // 🚨 BUG DETECTOR: Property $this->cloudinary tidak ada di Controller
        $doc = Documentation::factory()->create();
        $file = UploadedFile::fake()->image('new_document.jpg');

        $payload = [
            'image' => $file,
            'alt_text' => 'Updated Alt with Image',
            'type' => 'medium', // <-- UBAH
            'gallery_id' => 99,
            'soft_order' => 1,
        ];

        $response = $this->postJson("/api/documentations/{$doc->id}?_method=PUT", $payload);

        // Akan Error 500 karena bug $this->cloudinary
        $response->assertStatus(200); 
    }

    public function test_can_delete_documentation()
    {
        $doc = Documentation::factory()->create();

        $response = $this->deleteJson("/api/documentations/{$doc->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
                 
        $this->assertDatabaseMissing('documentations', ['id' => $doc->id]);
    }

    public function test_can_reorder_documentations()
    {
        $doc1 = Documentation::factory()->create(['soft_order' => 0]);
        $doc2 = Documentation::factory()->create(['soft_order' => 1]);

        $payload = [
            ['id' => $doc1->id, 'soft_order' => 5],
            ['id' => $doc2->id, 'soft_order' => 10],
        ];

        $response = $this->patchJson('/api/documentations/reorder', $payload);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('documentations', ['id' => $doc1->id, 'soft_order' => 5]);
        $this->assertDatabaseHas('documentations', ['id' => $doc2->id, 'soft_order' => 10]);
    }
}