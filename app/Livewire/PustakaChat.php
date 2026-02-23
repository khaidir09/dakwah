<?php

namespace App\Livewire;

use App\Models\ChatSession;
use App\Models\Library;
use App\Services\OpenNotebookService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class PustakaChat extends Component
{
    public $pustakaId;
    public string $message = '';
    // public array $messages = [];

    public $chatSession;
    public $isLoading = false;

    protected OpenNotebookService $service;

    protected $listeners = ['refreshChat' => '$refresh'];

    public function mount($pustakaId)
    {
        $this->pustakaId = $pustakaId;

        $this->loadSession();
    }

    public function loadSession()
    {
        if (Auth::check()) {
            $this->chatSession = ChatSession::with('messages')
                ->where('user_id', Auth::id())
                ->where('library_id', $this->pustakaId)
                ->first();
        }
    }

    // Property computed agar pesan selalu fresh dari DB saat render ulang
    public function getMessagesProperty()
    {
        if (!$this->chatSession) return collect([]);
        // Urutkan dari yang terlama ke terbaru (ASC) agar percakapan runut
        return $this->chatSession->messages()->oldest()->get();
    }

    private function stripMarkdown($text)
    {
        // 1. Hapus Bold (**teks**) dan Italic (*teks*) - Sisakan isinya
        $text = preg_replace('/(\*{1,2})(.*?)\1/', '$2', $text);

        // 2. Hapus Header Markdown (# Judul)
        $text = preg_replace('/^#+\s+/m', '', $text);

        // 3. Hapus List Bullet (* Item atau - Item) di awal baris
        $text = preg_replace('/^\s*[\*\-]\s+/m', '', $text);

        $text = preg_replace('/\[(source|note|insight):[a-zA-Z0-9]+\]/', '', $text);

        // 4. Hapus karakter Markdown sisa yang tidak diinginkan (opsional)
        // $text = str_replace('*', '', $text); 

        return trim($text);
    }

    public function ask(OpenNotebookService $service)
    {
        if (!Auth::check()) {
            $this->addError('message', 'Silakan login untuk bertanya.');
            return;
        }

        $this->validate([
            'message' => 'required|string|min:2'
        ]);

        // Limit harian: 5 pertanyaan per user per hari
        $rateLimitKey = 'pustaka-chat-limit:' . Auth::id() . ':' . now()->format('Y-m-d');

        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            $this->addError('question', 'Maaf, Anda telah mencapai batas 10 pertanyaan hari ini.');
            return;
        }

        $pustaka = Library::findOrFail($this->pustakaId);

        RateLimiter::hit($rateLimitKey, 86400);

        $this->isLoading = true;

        try {

            /*
        |--------------------------------------------------------------------------
        | 1️⃣ Pastikan Chat Session Ada
        |--------------------------------------------------------------------------
        */
            if (!$this->chatSession) {

                $openSession = $service->createSourceSession(
                    $pustaka->open_notebook_source_id
                );

                if (empty($openSession['id'])) {
                    throw new \Exception('Gagal membuat Open Notebook session.');
                }

                $this->chatSession = ChatSession::create([
                    'user_id' => Auth::id(),
                    'library_id' => $pustaka->id,
                    'open_notebook_session_id' => $openSession['id'],
                ]);
            }

            /*
        |--------------------------------------------------------------------------
        | 2️⃣ Simpan Pesan User ke DB Lokal
        |--------------------------------------------------------------------------
        */
            $userQuestion = $this->message;

            $this->chatSession->messages()->create([
                'role' => 'user',
                'message' => $userQuestion
            ]);

            $this->message = '';

            /*
        |--------------------------------------------------------------------------
        | 3️⃣ Kirim ke Open Notebook
        |--------------------------------------------------------------------------
        */
            logger([
                'DEBUG_SEND' => [
                    'source_id' => $pustaka->open_notebook_source_id,
                    'session_id' => $this->chatSession->open_notebook_session_id,
                    'question' => $userQuestion
                ]
            ]);

            $service->sendMessage(
                $pustaka->open_notebook_source_id,
                $this->chatSession->open_notebook_session_id,
                $userQuestion
            );

            /*
        |--------------------------------------------------------------------------
        | 4️⃣ Tunggu AI Generate (penting)
        |--------------------------------------------------------------------------
        */
            usleep(800000); // 0.8 detik

            /*
        |--------------------------------------------------------------------------
        | 5️⃣ Ambil Session Terbaru
        |--------------------------------------------------------------------------
        */
            $session = $service->getSession(
                $pustaka->open_notebook_source_id,
                $this->chatSession->open_notebook_session_id
            );

            logger(['DEBUG_SESSION' => $session]);

            /*
        |--------------------------------------------------------------------------
        | 6️⃣ Ambil Jawaban AI
        |--------------------------------------------------------------------------
        */
            $aiAnswer = null;

            if (!empty($session['messages']) && is_array($session['messages'])) {

                foreach (array_reverse($session['messages']) as $msg) {
                    if (
                        isset($msg['type']) &&
                        $msg['type'] === 'ai' &&
                        !empty($msg['content'])
                    ) {
                        $aiAnswer = $msg['content'];
                        break;
                    }
                }
            }

            if (!$aiAnswer) {
                $aiAnswer = 'Maaf, saya tidak dapat menemukan jawaban dalam dokumen ini.';
            }

            /*
        |--------------------------------------------------------------------------
        | 7️⃣ Simpan Jawaban AI
        |--------------------------------------------------------------------------
        */
            $cleanMessage = $this->stripMarkdown($aiAnswer);

            $this->chatSession->messages()->create([
                'role' => 'ai',
                'message' => $cleanMessage
            ]);
        } catch (\Throwable $e) {

            Log::error('PustakaChat Error: ' . $e->getMessage());

            if ($this->chatSession) {
                $this->chatSession->messages()->create([
                    'role' => 'ai',
                    'message' => 'Terjadi kesalahan teknis. Silakan coba lagi nanti.'
                ]);
            }
        }

        $this->isLoading = false;

        $this->dispatch('refreshChat');
    }

    public function render()
    {
        $remaining = 10;
        if (Auth::check()) {
            $rateLimitKey = 'pustaka-chat-limit:' . Auth::id() . ':' . now()->format('Y-m-d');
            $remaining = max(0, 10 - RateLimiter::attempts($rateLimitKey));
        }
        return view('livewire.pustaka-chat', [
            // Kirim pesan lewat computed property

            'messages' => $this->messages,
            'remaining' => $remaining
        ]);
    }
}
