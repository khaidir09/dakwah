<?php

namespace App\Livewire;

use App\Models\Library;
use Livewire\Component;
use App\Models\ChatSession;
use Illuminate\Support\Facades\Auth;
use App\Services\OpenNotebookService;
use Illuminate\Support\Facades\Session;

class PustakaChat extends Component
{
    public $pustakaId;
    public $question = '';
    // public $messages = [];

    public $chatSession;
    public $isLoading = false;

    // Kita simpan session_id chat di property public (Livewire akan menjaganya tetap ada selama user di halaman ini)
    // public $chatSessionId = null;

    public function mount($pustakaId)
    {
        $this->pustakaId = $pustakaId;

        // 1. Cek apakah User sudah pernah chat di Pustaka ini?
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

    public function ask(OpenNotebookService $aiService)
    {
        // Validasi Login
        if (!Auth::check()) {
            // Opsional: Redirect ke login atau tampilkan pesan error
            $this->addError('question', 'Silakan login untuk bertanya.');
            return;
        }

        $this->validate(['question' => 'required|string|min:2']);

        $pustaka = Library::find($this->pustakaId);

        $this->isLoading = true;

        try {
            // 2. Cek apakah sesi chat sudah ada? Jika belum, buat baru.
            if (!$this->chatSession) {
                // A. Minta Session ID ke Open Notebook API
                $apiSessionId = $aiService->createSession($pustaka->notebook_id);

                if (!$apiSessionId) throw new \Exception("Gagal membuat sesi chat di AI.");

                // B. Simpan Sesi ke Database Lokal
                $this->chatSession = ChatSession::create([
                    'user_id' => Auth::id(),
                    'library_id' => $this->pustakaId,
                    'open_notebook_session_id' => $apiSessionId
                ]);
            }

            // 3. Simpan Pesan User ke Database
            $this->chatSession->messages()->create([
                'role' => 'user',
                'message' => $this->question
            ]);

            // Simpan pertanyaan sementara dan reset input
            $userQuestion = $this->question;
            $this->question = '';

            // 4. Kirim ke API AI
            $response = $aiService->sendMessage(
                $this->chatSession->open_notebook_session_id,
                $userQuestion
            );

            // 5. Ambil Jawaban AI (Parsing Logika Sebelumnya)
            $aiAnswer = 'Maaf, tidak ada jawaban.';
            if (isset($response['messages']) && is_array($response['messages'])) {
                $lastMessage = end($response['messages']);
                if (isset($lastMessage['content'])) {
                    $aiAnswer = $lastMessage['content'];
                }
            }

            // 6. Simpan Jawaban AI ke Database
            $this->chatSession->messages()->create([
                'role' => 'ai',
                'message' => $aiAnswer
            ]);
        } catch (\Exception $e) {
            // Jika error, simpan pesan error sebagai system message atau flash
            // Disini kita biarkan saja user tau lewat UI loading yang berhenti
            session()->flash('error', 'Gagal: ' . $e->getMessage());
        }

        $this->isLoading = false;
    }

    // protected function addMessage($role, $text)
    // {
    //     $this->messages[] = [
    //         'role' => $role,
    //         'text' => $text,
    //         'time' => now()->format('H:i')
    //     ];
    // }

    public function render()
    {
        return view('livewire.pustaka-chat', [
            // Kirim pesan lewat computed property
            'messages' => $this->messages
        ]);
    }
}
