<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\ScheduleNote;
use App\Models\User;

class CatatanPengajianList extends Component
{
    public $perPage = 10;

    public function loadMore()
    {
        $this->perPage += 10;
    }

    public function render()
    {
        // Top 3 Pencatat Terbanyak (visibility = 'Public', status = 'Approved')
        $topUsers = User::withCount(['scheduleNotes as notes_count' => function ($query) {
            $query->where('visibility', 'Public')
                ->where('status', 'Approved');
        }])
            ->whereHas('scheduleNotes', function ($query) {
                $query->where('visibility', 'Public')
                    ->where('status', 'Approved');
            })
            ->orderByDesc('notes_count')
            ->limit(3)
            ->get();

        // List catatan pengajian dari terbaru, max sesuai perPage
        $notes = ScheduleNote::with(['user', 'schedule.assembly'])
            ->where('visibility', 'Public')
            ->where('status', 'Approved')
            ->latest()
            ->take($this->perPage)
            ->get();

        $totalNotes = ScheduleNote::where('visibility', 'Public')
            ->where('status', 'Approved')
            ->count();

        return view('livewire.user.catatan-pengajian-list', [
            'topUsers' => $topUsers,
            'notes' => $notes,
            'hasMore' => $this->perPage < $totalNotes,
        ]);
    }
}
