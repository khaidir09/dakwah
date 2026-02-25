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

    public function test_library_detail_displays_podcast_player_and_tabs_when_data_exists()
    {
        // Arrange
        $library = Library::create([
            'title' => 'Test Podcast Library',
            'slug' => 'test-podcast-library',
            'category' => 'General',
            'description' => 'A test description',
            'file_path' => 'books/test.pdf',
            'podcast_audio_path' => 'podcasts/test-episode.mp3',
            'podcast_metadata' => [
                'outline' => [
                    ['time' => '00:00', 'topic' => 'Intro Topic'],
                    ['time' => '05:00', 'topic' => 'Main Topic']
                ],
                'transcript' => [
                    ['speaker' => 'Host', 'text' => 'Welcome to the show.', 'timestamp' => '00:00'],
                    ['speaker' => 'Guest', 'text' => 'Thanks for having me.', 'timestamp' => '00:10']
                ]
            ],
            'is_active' => true,
        ]);

        // Act
        $response = $this->get(route('pustaka-detail', $library));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Podcast');
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
        $response->assertDontSee('Podcast'); // Should not see the podcast header (make sure "Podcast" word isn't in description/title)
        $response->assertDontSee('audio controls', false);
    }
}
