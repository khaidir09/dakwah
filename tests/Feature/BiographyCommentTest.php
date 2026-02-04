<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Teacher;
use App\Livewire\Biography\CommentSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BiographyCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_login_message()
    {
        $biography = Teacher::create([
            'name' => 'Syekh Abdul Qadir',
            'slug' => 'syekh-abdul-qadir',
            'biografi' => 'Biography content',
            'foto' => 'default.jpg',
            'domisili' => 'Baghdad',
        ]);

        Livewire::test(CommentSection::class, ['biography' => $biography])
            ->assertSee('Masuk untuk memberi komentar');
    }

    public function test_authenticated_user_can_comment()
    {
        $user = User::factory()->create();
        $biography = Teacher::create([
            'name' => 'Syekh Abdul Qadir',
            'slug' => 'syekh-abdul-qadir',
            'biografi' => 'Biography content',
            'foto' => 'default.jpg',
            'domisili' => 'Baghdad',
        ]);

        Livewire::actingAs($user)
            ->test(CommentSection::class, ['biography' => $biography])
            ->set('body', 'This is a test comment')
            ->call('save')
            ->assertSet('body', '');

        $this->assertDatabaseHas('comments', [
            'body' => 'This is a test comment',
            'user_id' => $user->id,
            'commentable_id' => $biography->id,
            'commentable_type' => Teacher::class,
        ]);
    }

    public function test_comment_is_displayed()
    {
        $user = User::factory()->create();
        $biography = Teacher::create([
            'name' => 'Syekh Abdul Qadir',
            'slug' => 'syekh-abdul-qadir',
            'biografi' => 'Biography content',
            'foto' => 'default.jpg',
            'domisili' => 'Baghdad',
        ]);

        $biography->comments()->create([
            'user_id' => $user->id,
            'body' => 'Existing comment',
        ]);

        Livewire::test(CommentSection::class, ['biography' => $biography])
            ->assertSee('Existing comment')
            ->assertSee($user->name);
    }
}
