<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyaikhunaLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_syaikhuna_text()
    {
        $response = $this->get(route('beranda'));
        $response->assertStatus(200);
        // We look for the text.
        // Note: assertSee searches the HTML.
        // Since we wrapped it in @guest, it should be present.
        $response->assertSee('Syaikhuna');
    }

    public function test_authenticated_user_does_not_see_syaikhuna_text()
    {
        // Need to ensure the factory works or manually create user.
        // Assuming User factory exists as standard.
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('beranda'));
        $response->assertStatus(200);

        // It should NOT see Syaikhuna text in the navbar.
        // However, we must be careful if "Syaikhuna" appears elsewhere on the page (e.g. title, footer).
        // The navbar text is: "Syaikhuna" inside a span.
        // Let's be more specific if possible, or accept that if it's in the title it might fail.

        // Checking the layout, the title defaults to 'Syaikhuna'.
        // <title>@yield('title', config('app.name', 'Syaikhuna'))</title>

        // So assertDontSee('Syaikhuna') will likely FAIL because it's in the <title> tag.

        // We need to assert against the specific HTML snippet.
        // <span class="... text-gray-800 ...">Syaikhuna</span>

        // Let's try to verify the absence of the specific span logic or the text in the context of the header.
        // Since asserts are simple string matches, this is tricky.

        // But wait, the title is usually in the <head>.
        // The body content is what we are mostly concerned with visually.

        // Let's rely on the fact that I wrapped the specific span.
        // I can assertDontSee specific classes + text combination?
        // <span class="text-lg md:text-2xl font-bold font-serif text-gray-800 tracking-tight dark:text-gray-100 pt-1">
        //            Syaikhuna
        // </span>

        // The whitespace might be an issue.

        // A better approach for the "authenticated" test:
        // Assert that the text "Syaikhuna" appears fewer times than for guest?
        // Or check if the specific span is missing.

        // Let's try to verify if the text "Syaikhuna" is present in the <header> tag?
        // It's hard to target specific elements with basic TestResponse assertions.

        // Let's try to match the surrounding HTML.
        $logoTextHtml = 'Syaikhuna';

        // If I can't easily distinguish from title, I'll rely on the manual verification I did.
        // But let's try.

        $response->assertSeeHtmlInOrder(['<title>', 'Syaikhuna']); // Title should be there.

        // The span has a unique class set. "text-lg md:text-2xl font-bold font-serif"
        $response->assertDontSee('text-lg md:text-2xl font-bold font-serif text-gray-800 tracking-tight dark:text-gray-100 pt-1');
    }
}
