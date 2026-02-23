<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenNotebookService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.open_notebook.base_url');
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE SOURCE CHAT SESSION
    |--------------------------------------------------------------------------
    */
    public function createSourceSession(string $sourceId): array
    {
        $response = Http::post(
            "{$this->baseUrl}/api/sources/{$sourceId}/chat/sessions",
            [
                'source_id' => $sourceId,
                'title' => 'Chat Pustaka',
            ]
        );

        return $response->json();
    }

    /*
    |--------------------------------------------------------------------------
    | SEND MESSAGE (NON-STREAMING)
    |--------------------------------------------------------------------------
    */
    public function sendMessage(
        string $sourceId,
        string $sessionId,
        string $message
    ): array {
        $response = Http::post(
            "{$this->baseUrl}/api/sources/{$sourceId}/chat/sessions/{$sessionId}/messages",
            [
                'message' => $message,
                'stream' => false // penting supaya bukan SSE
            ]
        );

        if (!$response->successful()) {
            throw new \Exception($response->body());
        }

        // Jika tidak ada JSON, kembalikan array kosong
        return $response->json() ?? [];
    }

    /*
    |--------------------------------------------------------------------------
    | GET SESSION WITH HISTORY
    |--------------------------------------------------------------------------
    */
    public function getSession(
        string $sourceId,
        string $sessionId
    ): array {
        $response = Http::get(
            "{$this->baseUrl}/api/sources/{$sourceId}/chat/sessions/{$sessionId}"
        );

        return $response->json();
    }
}
