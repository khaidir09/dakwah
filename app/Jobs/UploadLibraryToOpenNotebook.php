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

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(public Library $library)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(OpenNotebookService $service): void
    {
        $service->uploadLibrary($this->library);
    }
}
