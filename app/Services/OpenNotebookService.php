<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OpenNotebookService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.open_notebook.base_url');
    }

    /**
     * Langkah 1: Buat Sesi Chat baru
     */
    public function createSession($notebookId)
    {
        // Endpoint: POST /chat/sessions [6]
        $response = Http::post("{$this->baseUrl}/api/chat/sessions", [
            'notebook_id' => $notebookId,
            // Opsional: Berikan nama agar mudah dilacak di dashboard admin
            'name' => 'Session-' . uniqid(),
        ]);

        if ($response->successful()) {
            return $response->json()['id'];
        }

        return null;
    }

    /**
     * Langkah 2: Kirim pesan DENGAN KONTEKS Pustaka Tertentu
     * 
     * @param string $sessionId ID Sesi dari langkah 1
     * @param string $message Pertanyaan user
     * @param string $sourceId ID Pustaka (Source) yang sedang dibuka user
     */
    public function sendMessage($sessionId, $message, $sourceId)
    {
        // 1. Siapkan Context: Mapping Source ID ke Level 'full'
        // "Chat: Full-Content Context... AI sees complete source text"
        $contextPayload = [
            $sourceId => 'full' // Instruksi agar AI membaca "Full Content" dari source ini
        ];

        // 2. Siapkan Payload Lengkap
        $payload = [
            'session_id' => $sessionId,
            'message'    => $message,
            'context'    => $contextPayload, // <-- Konteks wajib masuk di sini
            'stream'     => false,
        ];

        try {
            // DEBUG: Log request outgoing
            Log::info('OpenNotebook Chat Request:', [
                'endpoint' => "{$this->baseUrl}/api/chat/execute",
                'payload_summary' => [
                    'session_id' => $sessionId,
                    'context_keys' => array_keys($contextPayload)
                ]
            ]);

            // 3. Eksekusi Request (Gunakan $this->client() agar Header Auth terbawa)
            // Source [4]: Endpoint POST /chat/execute
            $response = Http::post("{$this->baseUrl}/api/chat/execute", $payload);

            // 4. Log Response
            Log::info('OpenNotebook Chat Response:', [
                'status' => $response->status(),
                // Hati-hati logging body penuh jika respon sangat panjang
                'snippet' => substr($response->body(), 0, 200) . '...'
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            // Jika gagal, log error body-nya
            Log::error('OpenNotebook API Fail:', ['body' => $response->body()]);
            throw new \Exception('Gagal mengirim pesan: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('OpenNotebook Connection Error: ' . $e->getMessage());
            throw $e;
        }
    }

    // Tambahkan method baru untuk mengecek apakah Source ID valid dan ada isinya
    public function checkSource($sourceId)
    {
        // Source [5]: GET /sources/{id}
        // Gunakan $this->client() untuk otentikasi
        $response = Http::get("{$this->baseUrl}/api/sources/{$sourceId}");

        Log::info('Source Check:', [
            'id' => $sourceId,
            'status' => $response->status(),
            'json' => $response->json()
        ]);

        return $response->json();
    }
}
