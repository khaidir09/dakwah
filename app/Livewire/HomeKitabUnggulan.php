<?php

namespace App\Livewire;

use App\Models\Book;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class HomeKitabUnggulan extends Component
{
    // Di-forget oleh BookController setiap kali kitab disimpan atau dihapus.
    public const CACHE_KEY = 'home:kitab-unggulan';

    public function render()
    {
        $books = Cache::remember(self::CACHE_KEY, now()->addMinutes(10), fn () => Book::query()
            ->active()
            ->featured()
            ->with([
                'teachers' => fn ($q) => $q->publiclyVisible(),
                'specialization',
            ])
            ->latest()
            ->take(6)
            ->get());

        return view('livewire.home-kitab-unggulan', [
            'books' => $books,
        ]);
    }
}
