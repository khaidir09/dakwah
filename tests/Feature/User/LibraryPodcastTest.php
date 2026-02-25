<?php

namespace Tests\Feature\User;

use App\Models\Library;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LibraryPodcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_podcast_content()
    {
        // Arrange
        $user = User::factory()->create();
        $library = Library::create([
            'title' => 'Test Podcast Library',
            'slug' => 'test-podcast-library',
            'category' => 'General',
            'description' => 'A test description',
            'file_path' => 'books/test.pdf',
            'podcast_audio_path' => 'podcasts/test-episode.mp3',
            'podcast_metadata' => [
                'outline' => [
                    ['name' => '00:00', 'description' => 'Intro Topic'],
                    ['name' => '05:00', 'description' => 'Main Topic']
                ],
                'transcript' => [
                    ['speaker' => 'Host', 'dialogue' => 'Welcome to the show.'],
                    ['speaker' => 'Guest', 'dialogue' => 'Thanks for having me.']
                ]
            ],
            'is_active' => true,
        ]);

        // Act
        $response = $this->actingAs($user)->get(route('pustaka-detail', $library));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Podcast AI');
        $response->assertSee('Outline');
        $response->assertSee('Transcript');

        // Check for audio source - we relax the check to just the path since Storage::url might vary
        $response->assertSee('podcasts/test-episode.mp3');

        // Check for outline content
        $response->assertSee('Intro Topic');
        $response->assertSee('Main Topic');

        // Check for transcript content
        $response->assertSee('Welcome to the show.');
        $response->assertSee('Thanks for having me.');
    }

    public function test_guest_cannot_access_podcast_content_and_sees_login_prompt()
    {
        // Arrange
        $library = Library::create([
            'title' => 'Test Podcast Library Guest',
            'slug' => 'test-podcast-library-guest',
            'category' => 'General',
            'description' => 'A test description',
            'file_path' => 'books/test.pdf',
            'podcast_audio_path' => 'podcasts/test-episode.mp3',
             'podcast_metadata' => [
                'outline' => [
                    ['name' => '00:00', 'description' => 'Intro Topic'],
                    ['name' => '05:00', 'description' => 'Main Topic']
                ],
                'transcript' => [
                    ['speaker' => 'Host', 'dialogue' => 'Welcome to the show.'],
                    ['speaker' => 'Guest', 'dialogue' => 'Thanks for having me.']
                ]
            ],
            'is_active' => true,
        ]);

        // Act
        $response = $this->get(route('pustaka-detail', $library));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Podcast AI'); // Header is visible

        // Content should be hidden
        $response->assertDontSee('podcasts/test-episode.mp3');
        $response->assertDontSee('Intro Topic');
        $response->assertDontSee('Welcome to the show.');

        // Login prompt should be visible
        $response->assertSee('Login untuk mendengarkan podcast');
        $response->assertSee(route('login'));
    }

    public function test_library_detail_does_not_display_podcast_section_when_no_audio()
    {
        // Arrange
        $library = Library::create([
            'title' => 'No Audio Library',
            'slug' => 'no-audio-library',
            'category' => 'General',
            'description' => 'A test description',
            'file_path' => 'books/test.pdf',
            'podcast_audio_path' => null, // No audio
            'podcast_metadata' => null,
            'is_active' => true,
        ]);

        // Act
        $response = $this->get(route('pustaka-detail', $library));

        // Assert
        $response->assertStatus(200);
        $response->assertDontSee('Podcast AI'); // Should not see the podcast header
        $response->assertDontSee('audio controls', false);
    }
}
