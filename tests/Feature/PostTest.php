<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Livewire\Post\Index;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_posts_index()
    {
        $admin = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'Super Admin']);
        $admin->assignRole('Super Admin');

        $response = $this->actingAs($admin)->get(route('admin.posts.index'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(Index::class);
    }

    public function test_author_can_access_managed_posts_index()
    {
        $author = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'Penulis']);
        $author->assignRole('Penulis');

        $response = $this->actingAs($author)->get(route('kelola-tulisan.index'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(Index::class);
    }

    public function test_public_can_see_published_posts()
    {
        $post = Post::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Content here',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('tulisan.list'));
        $response->assertStatus(200);
        $response->assertSee('Test Post');
    }

    public function test_author_can_create_post()
    {
        Storage::fake('public');
        $author = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'Penulis']);
        $author->assignRole('Penulis');

        $file = UploadedFile::fake()->image('cover.jpg');

        $response = $this->actingAs($author)->post(route('kelola-tulisan.store'), [
            'title' => 'New Post',
            'content' => 'New Content',
            'status' => 'draft',
            'labels' => 'Tag1, Tag2',
            'cover_image' => $file,
        ]);

        $response->assertRedirect(route('kelola-tulisan.index'));
        $this->assertDatabaseHas('posts', ['title' => 'New Post']);
        $this->assertDatabaseHas('labels', ['name' => 'Tag1']);
    }
}
