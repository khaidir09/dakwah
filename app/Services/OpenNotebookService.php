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

    /**
     * Langkah 1: Buat Sesi Chat baru yang terhubung ke Notebook tertentu
     */
    public function createSession($notebookId)
    {
        try {
            $response = Http::post("{$this->baseUrl}/api/chat/sessions", [
                'notebook_id' => $notebookId,
                'name' => 'Session-' . uniqid(),
            ]);

            if ($response->successful()) {
                return $response->json()['id'];
            }

            Log::error('OpenNotebook Create Session Failed:', ['body' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('OpenNotebook Connection Error (CreateSession): ' . $e->getMessage());
            return null;
        }
    }

    public function sendMessage($sessionId, $notebookId, $message, $sourceId)
    {
        $contextConfig = [
            'sources' => [
                $sourceId => 'full' // Key harus berupa "source:ID_ANDA"
            ]
        ];

        try {
            // Panggil API Context
            $buildResponse = Http::post("{$this->baseUrl}/api/chat/context", [
                'notebook_id' => $notebookId,
                'context_config' => $contextConfig
            ]);

            if (!$buildResponse->successful()) {
                throw new \Exception("Gagal membangun konteks: " . $buildResponse->body());
            }

            $builtContext = $buildResponse->json();

            // LOGGING HASIL CONTEXT (Untuk verifikasi ekspektasi Anda)
            // Cek laravel.log, Anda akan melihat 'full_text' di sini
            Log::info('OpenNotebook Context Built:', [
                'char_count' => $builtContext['char_count'] ?? 0,
                'has_text' => !empty($builtContext['sources'][0]['full_text']),
                // Uncomment baris bawah jika ingin melihat full text di log (bisa sangat panjang)
                // 'preview' => $builtContext
            ]);

            // Validasi: Jika char_count 0, berarti source tidak terhubung atau kosong
            if (($builtContext['char_count'] ?? 0) === 0) {
                Log::warning("OpenNotebook: Konteks kosong. Pastikan Source ID $sourceId terhubung ke Notebook ID $notebookId");
            }

            // 3. SIAPKAN PAYLOAD CHAT
            $payload = [
                'session_id' => $sessionId,
                'message'    => $message,
                'context'    => $builtContext, // Masukkan hasil JSON context lengkap ke sini
                'stream'     => false,
            ];

            // 4. EKSEKUSI CHAT
            // Gunakan timeout lebih lama untuk generasi teks
            $chatResponse = Http::timeout(300)->post("{$this->baseUrl}/api/chat/execute", $payload);

            if ($chatResponse->successful()) {
                return $chatResponse->json();
            }

            throw new \Exception('API Error: ' . $chatResponse->body());
        } catch (\Exception $e) {
            Log::error('OpenNotebook sendMessage Error: ' . $e->getMessage());
            throw $e;
        }
    }

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
    }
}
