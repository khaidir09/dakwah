<?php

namespace App\Livewire;

use App\Models\ChatSession;
use App\Models\Library;
use App\Services\OpenNotebookService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class PustakaChat extends Component
{
    public $pustakaId;
    public $question = '';
    // public $messages = [];

    public $chatSession;
    public $isLoading = false;

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
            // 3. Cek/Buat Sesi Chat
            if (!$this->chatSession) {
                // Service otomatis menangani prefix 'notebook:'
                $apiSessionId = $aiService->createSession($pustaka->notebook_id);

                if (!$apiSessionId) {
                    throw new \Exception("Gagal menghubungkan ke layanan AI.");
                }

                $this->chatSession = ChatSession::create([
                    'user_id' => Auth::id(),
                    'library_id' => $this->pustakaId,
                    'open_notebook_session_id' => $apiSessionId
                ]);
            }

            // 4. Simpan Pesan User (UI Update)
            $this->chatSession->messages()->create([
                'role' => 'user',
                'message' => $this->question
            ]);

            $userQuestion = $this->question;
            $this->question = ''; // Reset input field

            // 5. Kirim ke Service (Logic utama ada di Service)
            // Service akan menormalisasi ID menjadi 'source:xxxx' dan 'notebook:xxxx'
            $response = $aiService->sendMessage(
                $this->chatSession->open_notebook_session_id,
                $pustaka->notebook_id,
                $userQuestion,
                $pustaka->open_notebook_source_id
            );

            // 6. Parsing Jawaban
            $aiAnswer = 'Maaf, saya tidak dapat menemukan jawaban dalam dokumen ini.';

            if (isset($response['messages']) && is_array($response['messages'])) {
                // Ambil pesan terakhir dari array
                $lastMessage = end($response['messages']);
                if (!empty($lastMessage['content'])) {
                    $aiAnswer = $lastMessage['content'];
                }
            }

            // 7. Simpan Jawaban AI
            $this->chatSession->messages()->create([
                'role' => 'ai',
                'message' => $aiAnswer
            ]);
        } catch (\Exception $e) {
            Log::error('PustakaChat Error: ' . $e->getMessage());

            // Berikan feedback visual tapi jangan break aplikasi
            if ($this->chatSession) {
                $this->chatSession->messages()->create([
                    'role' => 'ai',
                    'message' => 'Terjadi kesalahan teknis. Silakan coba lagi nanti.'
                ]);
            } else {
                session()->flash('error', 'Gagal memproses percakapan.');
            }
        }

        $this->isLoading = false;

        $this->dispatch('refreshChat');
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
