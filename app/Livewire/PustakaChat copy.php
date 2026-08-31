<?php

namespace App\Livewire;

use App\Models\ChatSession;
use App\Models\Library;
use App\Services\OpenNotebookService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

    private function buildHistory(): array
    {
        return $this->chatSession
            ->messages()
            ->orderBy('created_at')
            ->get()
            ->map(function ($msg) {
                return [
                    'type' => $msg->role === 'user' ? 'human' : 'ai',
                    'content' => $msg->message
                ];
            })
            ->toArray();
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

        $pustaka = Library::findOrFail($this->pustakaId);

        $this->isLoading = true;

        try {

            // 1️⃣ Pastikan chatSession lokal ada
            if (!$this->chatSession) {
                $this->chatSession = ChatSession::create([
                    'user_id' => Auth::id(),
                    'library_id' => $pustaka->id,
                ]);
            }

            // 2️⃣ Pastikan Open Notebook session ada
            if (!$this->chatSession->open_notebook_session_id) {

                $openSession = $service->createSourceSession(
                    $pustaka->open_notebook_source_id
                );

                if (empty($openSession['id'])) {
                    throw new \Exception('Gagal membuat Open Notebook session.');
                }

                $this->chatSession->update([
                    'open_notebook_session_id' => $openSession['id'],
                ]);

                // refresh object
                $this->chatSession->refresh();
            }

            $userQuestion = $this->message;

            // 2️⃣ Simpan pesan user
            $this->chatSession->messages()->create([
                'role' => 'user',
                'message' => $userQuestion
            ]);

            $this->message = '';

            // 3️⃣ Execute chat
            $response = $service->executeChat(
                $pustaka->notebook_id,
                $pustaka->open_notebook_source_id,
                $this->chatSession->open_notebook_session_id,
                $userQuestion
            );

            logger(['EXECUTE_RESPONSE' => $response]);

            // 4️⃣ Ambil jawaban
            $aiAnswer = $response['message']
                ?? 'Maaf, saya tidak dapat menemukan jawaban dalam dokumen ini.';

            // 5️⃣ Simpan jawaban
            $this->chatSession->messages()->create([
                'role' => 'ai',
                'message' => $aiAnswer
            ]);
        } catch (\Throwable $e) {

            Log::error('PustakaChat Error: ' . $e->getMessage());

            $this->chatSession?->messages()->create([
                'role' => 'ai',
                'message' => 'Terjadi kesalahan teknis. Silakan coba lagi nanti.'
            ]);
        }

        $this->isLoading = false;

        $this->dispatch('refreshChat');
    }

    public function render()
    {
        return view('livewire.pustaka-chat', [
            // Kirim pesan lewat computed property
            'messages' => $this->messages
        ]);
    }
}
