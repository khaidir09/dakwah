<?php

namespace App\Livewire\Library;

use App\Models\Library;
use App\Services\OpenNotebookService;
use Livewire\Component;

class Chat extends Component
{
    public Library $library;
    public array $messages = [];
    public string $input = '';

    public function mount(Library $library)
    {
        $this->library = $library;
    }

    public function sendMessage()
    {
        $this->validate([
            'input' => 'required|string|max:1000',
        ]);

        $userMessage = $this->input;

        // Optimistically add user message? No, simpler to do it in one go.
        // We add it to the array so it renders.
        $this->messages[] = ['role' => 'user', 'content' => $userMessage];

        // Clear input immediately
        $this->input = '';

        try {
            $service = app(OpenNotebookService::class);
            // Assuming the source_id is the library ID as per upload logic
            $response = $service->chat($userMessage, (string) $this->library->id, $this->library->notebook_id);

            $aiContent = $response['answer'] ?? 'Maaf, saya tidak dapat menjawab saat ini.';

            $this->messages[] = ['role' => 'ai', 'content' => $aiContent];
        } catch (\Exception $e) {
            $this->messages[] = ['role' => 'ai', 'content' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }

        $this->dispatch('message-sent');
    }

    public function render()
    {
        return view('livewire.library.chat');
    }
}
