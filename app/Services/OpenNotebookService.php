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
        // 1. Buat "Strict Prompt" (Instruksi Tegas)
        // Kita tempelkan instruksi ini TEPAT SEBELUM pertanyaan user.
        // Teknik ini disebut "System Prompt Override" via User Message.

        $strictInstruction =
            "PERAN: Anda adalah asisten pustaka yang membantu menjelaskan isi dokumen.\n" .
            "INSTRUKSI:\n" .
            "1. Jawab pertanyaan pengguna dengan menggunakan informasi dari 'Context' yang tersedia di bawah ini sebagai sumber utama.\n" .
            "2. Anda BOLEH merangkum, menyimpulkan, atau memfrasekan ulang kalimat dari dokumen agar lebih mudah dipahami, selama maknanya tidak berubah.\n" .
            "3. JANGAN menambahkan fakta baru (seperti angka, tanggal, hukum) yang tidak disebutkan dalam dokumen.\n" .
            "4. Jika informasi benar-benar tidak ada di dokumen, katakan dengan sopan bahwa topik tersebut belum tersedia di pustaka.\n\n" .
            "PERTANYAAN PENGGUNA:\n";

        // Gabungkan instruksi dengan pesan asli user
        $finalMessage = $strictInstruction . $message;

        // Endpoint: POST /api/chat/execute
        $response = Http::timeout(60)->post("{$this->baseUrl}/api/chat/execute", [
            'session_id' => $sessionId,
            'message' => $finalMessage,
            'context'    => (object)[],
            // 'stream' => false, // Matikan stream agar respons langsung JSON utuh
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Gagal mengirim pesan: ' . $response->body());
    }
}
