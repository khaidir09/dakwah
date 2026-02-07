<?php

namespace App\Observers;

use App\Jobs\UploadLibraryToOpenNotebook;
use App\Models\Library;

class LibraryObserver
{
    /**
     * Handle the Library "created" event.
     */
    public function created(Library $library): void
    {
        if ($library->file_path) {
            UploadLibraryToOpenNotebook::dispatch($library);
        }
    }

    /**
     * Handle the Library "updated" event.
     */
    public function updated(Library $library): void
    {
        if ($library->isDirty('file_path') && $library->file_path) {
            UploadLibraryToOpenNotebook::dispatch($library);
        }
    }
}
