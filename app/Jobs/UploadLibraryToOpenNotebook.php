<?php

namespace App\Jobs;

use App\Models\Library;
use App\Services\OpenNotebookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UploadLibraryToOpenNotebook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $library;

    public function __construct(Library $library)
    {
        $this->library = $library;
    }

    public function handle()
    {
        $service = new OpenNotebookService();
        $service->uploadLibrary($this->library);
    }
}
