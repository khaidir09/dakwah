<?php

namespace App\Livewire;

use App\Models\Library;
use Livewire\Component;
use App\Models\ChatSession;
use Illuminate\Support\Facades\Auth;
use App\Services\OpenNotebookService;
use Illuminate\Support\Facades\RateLimiter;

class PustakaChat extends Component
{
    public $pustakaId;
    public $question = '';
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

    public function getMessagesProperty()
    {
        if (!$this->chatSession) return collect([]);
        return $this->chatSession->messages()->oldest()->get();
    }

    public function ask(OpenNotebookService $aiService)
    {
        if (!Auth::check()) {
            $this->addError('question', 'Silakan login untuk bertanya.');
            return;
        }

        $this->validate(['question' => 'required|string|min:2']);

        $rateLimitKey = 'pustaka-chat-limit:' . Auth::id() . ':' . now()->format('Y-m-d');
        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            $this->addError('question', 'Maaf, Anda telah mencapai batas 10 pertanyaan hari ini.');
            return;
        }

        $pustaka = Library::find($this->pustakaId);

        if (!$pustaka || empty($pustaka->open_notebook_source_id) || empty($pustaka->notebook_id)) {
            session()->flash('error', 'Dokumen pustaka ini belum terhubung sepenuhnya ke sistem AI (ID tidak lengkap).');
            return;
        }

        RateLimiter::hit($rateLimitKey, 86400);

        $this->isLoading = true;

        try {
            if (!$this->chatSession) {
                $apiSessionId = $aiService->createSession($pustaka->notebook_id);

                if (!$apiSessionId) throw new \Exception("Gagal inisialisasi sesi AI.");

                $this->chatSession = ChatSession::create([
                    'user_id' => Auth::id(),
                    'library_id' => $this->pustakaId,
                    'open_notebook_session_id' => $apiSessionId
                ]);
            }

            $this->chatSession->messages()->create([
                'role' => 'user',
                'message' => $this->question
            ]);

            $userQuestion = $this->question;
            $this->question = '';

            $response = $aiService->executeChat(
                $this->chatSession->open_notebook_session_id,
                $userQuestion,
                $pustaka->open_notebook_source_id
            );

            $aiAnswer = 'Maaf, saya tidak dapat menemukan jawaban dari dokumen ini.';

            if (isset($response['messages']) && is_array($response['messages'])) {
                $lastMessage = end($response['messages']);

                if (!empty($lastMessage['content'])) {
                    $aiAnswer = $lastMessage['content'];
                }
            }
            $this->chatSession->messages()->create([
                'role' => 'ai',
                'message' => $aiAnswer
            ]);
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        $this->isLoading = false;
    }
}
