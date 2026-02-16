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

    // Listener untuk handle error (opsional)
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

    // Computed Property untuk pesan agar reaktif
    public function getMessagesProperty()
    {
        if (!$this->chatSession) return collect([]);
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

        // 4. Hapus karakter Markdown sisa yang tidak diinginkan (opsional)
        // $text = str_replace('*', '', $text); 

        return trim($text);
    }

    public function ask(OpenNotebookService $aiService)
    {
        // 1. Validasi
        if (!Auth::check()) {
            $this->addError('question', 'Silakan login untuk bertanya.');
            return;
        }

        $this->validate(['question' => 'required|string|min:2']);

        // Limit harian: 5 pertanyaan per user per hari
        $rateLimitKey = 'pustaka-chat-limit:' . Auth::id() . ':' . now()->format('Y-m-d');
        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            $this->addError('question', 'Maaf, Anda telah mencapai batas 10 pertanyaan hari ini.');
            return;
        }

        // Ambil data Pustaka
        $pustaka = Library::find($this->pustakaId);

        // DEBUG 3: Pastikan ID Source di database Anda BENAR dan TIDAK KOSONG
        if (!$pustaka || empty($pustaka->open_notebook_source_id) || empty($pustaka->notebook_id)) {
            session()->flash('error', 'Dokumen pustaka ini belum terhubung sepenuhnya ke sistem AI (ID tidak lengkap).');
            return;
        }

        // Hit rate limiter
        RateLimiter::hit($rateLimitKey, 86400);

        $this->isLoading = true;

        try {
            // 2. Cek atau Buat Sesi Baru
            if (!$this->chatSession) {
                // Buat sesi di API Open Notebook
                // Note: Session biasanya butuh notebook_id sebagai container utama
                $apiSessionId = $aiService->createSession($pustaka->notebook_id);

                if (!$apiSessionId) throw new \Exception("Gagal inisialisasi sesi AI.");

                // Simpan sesi di DB Lokal
                $this->chatSession = ChatSession::create([
                    'user_id' => Auth::id(),
                    'library_id' => $this->pustakaId,
                    'open_notebook_session_id' => $apiSessionId
                ]);
            }

            // 3. Simpan Pesan User ke Database Lokal (Optimistic UI)
            $this->chatSession->messages()->create([
                'role' => 'user',
                'message' => $this->question
            ]);

            // Simpan pertanyaan sementara dan reset input
            $userQuestion = $this->question;
            $this->question = '';

            // EKSEKUSI UTAMA
            // Kita panggil fungsi checkSource dulu untuk memastikan file terbaca di API
            // Uncomment baris di bawah ini jika ingin mengecek status file sekali saja
            // dd($aiService->checkSource($pustaka->open_notebook_source_id));

            $response = $aiService->sendMessage(
                $this->chatSession->open_notebook_session_id,
                $userQuestion, // Gunakan variabel yang sudah diamankan
                $pustaka->open_notebook_source_id
            );

            // 5. Parsing Jawaban AI
            $aiAnswer = 'Maaf, saya tidak dapat menemukan jawaban dari dokumen ini.';

            if (isset($response['messages']) && is_array($response['messages'])) {
                // Ambil pesan terakhir
                $lastMessage = end($response['messages']);

                // Validasi tambahan: Pastikan ada kontennya
                if (!empty($lastMessage['content'])) {
                    $aiAnswer = $lastMessage['content'];
                }
            }

            // --- BERSIHKAN MARKDOWN DI SINI ---
            $cleanMessage = $this->stripMarkdown($aiAnswer);

            // 6. Simpan Jawaban AI ke Database Lokal
            $this->chatSession->messages()->create([
                'role' => 'ai',
                'message' => $cleanMessage
            ]);
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        $this->isLoading = false;
    }

    public function render()
    {
        $remaining = 10;
        if (Auth::check()) {
            $rateLimitKey = 'pustaka-chat-limit:' . Auth::id() . ':' . now()->format('Y-m-d');
            $remaining = max(0, 10 - RateLimiter::attempts($rateLimitKey));
        }

        return view('livewire.pustaka-chat', [
            'messages' => $this->messages,
            'remaining' => $remaining
        ]);
    }
}
