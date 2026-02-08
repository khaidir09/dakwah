<?php

namespace App\Services;

use App\Models\Library;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class OpenNotebookService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $defaultNotebookId;

    public function __construct()
    {
        $this->baseUrl = config('services.open_notebook.base_url');
        $this->apiKey = config('services.open_notebook.api_key');
        $this->defaultNotebookId = config('services.open_notebook.default_id');
    }

    public function uploadLibrary(Library $library)
    {
        if (!$library->file_path || !Storage::disk('public')->exists($library->file_path)) {
            return;
        }

        $fileContent = Storage::disk('public')->get($library->file_path);
        $fileName = basename($library->file_path);

        $notebookId = $library->notebook_id ?? $this->defaultNotebookId;

        $response = Http::withToken($this->apiKey)
            ->attach('file', $fileContent, $fileName)
            ->post($this->baseUrl . '/libraries', [
                'title' => $library->title,
                'category' => $library->category,
                'description' => $library->description,
                'source_id' => (string) $library->id,
                'notebook_id' => $notebookId,
            ]);

        return $response->json();
    }

    public function chat(string $query, string $sourceId, ?string $notebookId = null)
    {
        $notebookId = $notebookId ?? $this->defaultNotebookId;

        $response = Http::withToken($this->apiKey)
            ->post($this->baseUrl . '/chat', [
                'query' => $query,
                'source_id' => $sourceId,
                'notebook_id' => $notebookId,
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        return ['answer' => 'Maaf, terjadi kesalahan saat menghubungi AI.'];
    }
}
