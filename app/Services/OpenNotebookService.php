<?php

namespace App\Services;

use App\Models\Library;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OpenNotebookService
{
    protected ?string $baseUrl;
    protected ?string $apiKey;
    protected ?string $notebookId;

    public function __construct()
    {
        $this->baseUrl = config('services.open_notebook.base_url');
        $this->apiKey = config('services.open_notebook.api_key');
        $this->notebookId = config('services.open_notebook.notebook_id');
    }

    /**
     * Upload a library PDF to Open Notebook for indexing.
     *
     * @param Library $library
     * @return array|bool
     */
    public function uploadLibrary(Library $library)
    {
        if (empty($this->baseUrl)) {
            Log::warning('Open Notebook Base URL not set.');
            return false;
        }

        if (empty($this->notebookId)) {
            Log::warning('Open Notebook ID not set.');
            return false;
        }

        if (!$library->file_path || !Storage::disk('public')->exists($library->file_path)) {
            Log::error("Library file not found for ID: {$library->id}");
            return false;
        }

        $filePath = Storage::disk('public')->path($library->file_path);

        try {
            $fileStream = fopen($filePath, 'r');

            $request = Http::attach(
                'file', $fileStream, basename($filePath)
            );

            if (!empty($this->apiKey)) {
                $request->withToken($this->apiKey);
            }

            $response = $request->post($this->baseUrl . '/libraries', [
                'title' => $library->title,
                'category' => $library->category,
                'description' => strip_tags($library->description),
                'external_id' => (string) $library->id,
                'source_id' => (string) $library->id,
                'notebook_id' => $this->notebookId,
            ]);

            if (is_resource($fileStream)) {
                fclose($fileStream);
            }

            if ($response->successful()) {
                Log::info("Library uploaded to Open Notebook: {$library->title}");
                return $response->json();
            } else {
                Log::error('Open Notebook Error: ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Open Notebook Exception: ' . $e->getMessage());
            return false;
        }
    }
}
