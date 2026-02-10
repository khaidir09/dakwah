<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenNotebookService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.open_notebook.base_url');
    }

    /**
     * Langkah 1: Buat Sesi Chat baru yang terhubung ke Notebook tertentu
     */
    public function createSession($notebookId)
    {
        // Endpoint: POST /api/chat/sessions
        $response = Http::post("{$this->baseUrl}/api/chat/sessions", [
            'notebook_id' => $notebookId,
            // Opsional: 'name' => 'Chat User X',
        ]);

        if ($response->successful()) {
            // Mengembalikan ID Sesi, misal: "sess_abc123"
            return $response->json()['id'];
        }

        return null;
    }

    /**
     * Langkah 2: Kirim pesan ke sesi yang sudah dibuat
     */
    public function sendMessage($sessionId, $message)
    {
        // Endpoint: POST /api/chat/execute
        $response = Http::timeout(60)->post("{$this->baseUrl}/api/chat/execute", [
            'session_id' => $sessionId,
            'message' => $message,
            'context'    => (object)[],
            // 'stream' => false, // Matikan stream agar respons langsung JSON utuh
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Gagal mengirim pesan: ' . $response->body());
    }
}
