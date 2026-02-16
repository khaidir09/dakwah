<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\OpenNotebookService;
use Illuminate\Support\Facades\Http;

class OpenNotebookServiceTest extends TestCase
{
    public function test_create_session_sends_title()
    {
        config(['services.open_notebook.base_url' => 'http://test.com']);

        Http::fake([
            '*/api/chat/sessions' => Http::response(['id' => 'session-123'], 200),
        ]);

        $service = new OpenNotebookService();
        $service->createSession('notebook-123');

        Http::assertSent(function ($request) {
            return $request->url() == 'http://test.com/api/chat/sessions' &&
                   $request['notebook_id'] == 'notebook-123' &&
                   isset($request['title']) && // Check for title
                   !isset($request['name']); // Should not have name
        });
    }

    public function test_send_message_sends_correct_context()
    {
        config(['services.open_notebook.base_url' => 'http://test.com']);

        Http::fake([
            '*/api/chat/execute' => Http::response(['messages' => [['content' => 'Hello']]], 200),
        ]);

        $service = new OpenNotebookService();
        $service->sendMessage('session-123', 'Hello?', 'source-456');

        Http::assertSent(function ($request) {
            return $request->url() == 'http://test.com/api/chat/execute' &&
                   $request['session_id'] == 'session-123' &&
                   isset($request['context']['sources']['source-456']) &&
                   $request['context']['sources']['source-456'] == 'full content';
        });
    }
}
