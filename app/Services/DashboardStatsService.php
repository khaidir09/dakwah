<?php

namespace App\Services;

use App\Models\Assembly;
use App\Models\Comment;
use App\Models\Event;
use App\Models\Library;
use App\Models\Post;
use App\Models\Schedule;
use App\Models\ScheduleNote;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Wirid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardStatsService
{
    private const CACHE_KEY = 'admin.dashboard.summary';

    private const CACHE_TTL = 600;

    private const DELTA_DAYS = 30;

    /**
     * KPI + aktivitas terbaru. Di-cache karena bersifat informatif —
     * basi beberapa menit tidak menghalangi admin bertindak.
     */
    public function summary(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => [
            'stats' => $this->stats(),
            'activity' => $this->activity(),
        ]);
    }

    /**
     * Antrian moderasi. Sengaja TIDAK di-cache: angka ini yang dipakai admin
     * untuk bertindak, jadi harus langsung turun setelah sesuatu disetujui.
     */
    public function moderationQueues(): array
    {
        return [
            [
                'type' => 'majelis',
                'label' => 'Majelis',
                'count' => $this->pendingAssemblies()->count(),
                'url' => route('majelis.index', ['tab' => 'moderasi']),
            ],
            [
                'type' => 'guru',
                'label' => 'Guru',
                'count' => $this->pendingTeachers()->count(),
                'url' => route('guru.index', ['tab' => 'moderasi']),
            ],
            [
                'type' => 'jadwal',
                'label' => 'Jadwal',
                'count' => $this->pendingSchedules()->count(),
                'url' => route('jadwal-majelis.index', ['tab' => 'moderasi']),
            ],
            [
                'type' => 'amalan',
                'label' => 'Amalan',
                'count' => $this->pendingWirids()->count(),
                'url' => route('wirid.index', ['tab' => 'moderasi']),
            ],
            [
                'type' => 'acara',
                'label' => 'Acara',
                'count' => $this->pendingEvents()->count(),
                'url' => route('event.index', ['tab' => 'moderasi']),
            ],
            [
                'type' => 'catatan',
                'label' => 'Catatan Pengajian',
                'count' => $this->pendingScheduleNotes()->count(),
                'url' => route('schedule-notes.index'),
            ],
        ];
    }

    /**
     * Item pending terbaru, digabung lintas jenis lalu diurutkan waktu.
     */
    public function latestPending(int $limit = 5): Collection
    {
        $items = collect()
            ->concat($this->pendingAssemblies()->with('contributor')->latest()->limit($limit)->get()
                ->map(fn (Assembly $a) => $this->item('majelis', 'Majelis', $a->nama_majelis, $a->contributor?->name, $a->created_at, route('majelis.index', ['tab' => 'moderasi']))))
            ->concat($this->pendingTeachers()->with('contributor')->latest()->limit($limit)->get()
                ->map(fn (Teacher $t) => $this->item('guru', 'Guru', $t->name, $t->contributor?->name, $t->created_at, route('guru.index', ['tab' => 'moderasi']))))
            ->concat($this->pendingSchedules()->with('contributor')->latest()->limit($limit)->get()
                ->map(fn (Schedule $s) => $this->item('jadwal', 'Jadwal', $s->nama_jadwal, $s->contributor?->name, $s->created_at, route('jadwal-majelis.index', ['tab' => 'moderasi']))))
            ->concat($this->pendingWirids()->with('contributor')->latest()->limit($limit)->get()
                ->map(fn (Wirid $w) => $this->item('amalan', 'Amalan', $w->nama, $w->contributor?->name, $w->created_at, route('wirid.index', ['tab' => 'moderasi']))))
            ->concat($this->pendingEvents()->with('contributor')->latest()->limit($limit)->get()
                ->map(fn (Event $e) => $this->item('acara', 'Acara', $e->name, $e->contributor?->name, $e->created_at, route('event.index', ['tab' => 'moderasi']))))
            ->concat($this->pendingScheduleNotes()->with(['user', 'schedule'])->latest()->limit($limit)->get()
                ->map(fn (ScheduleNote $n) => $this->item('catatan', 'Catatan', $n->schedule?->nama_jadwal, $n->user?->name, $n->created_at, route('schedule-notes.index'))));

        return $this->newest($items, $limit);
    }

    private function stats(): array
    {
        return [
            $this->card('Pengguna', User::query()),
            $this->card('Majelis', Assembly::publiclyVisible()),
            $this->card('Guru', Teacher::publiclyVisible()),
            $this->card('Jadwal', Schedule::publiclyVisible()),
            $this->card('Acara', Event::publiclyVisible()),
            $this->card('Amalan', Wirid::publiclyVisible()),
            $this->card('Tulisan', Post::published()),
            $this->card('Pustaka', Library::query()),
        ];
    }

    /**
     * Total memakai semantik "konten hidup" (lihat scope publiclyVisible),
     * dan delta membandingkan 30 hari terakhir dengan 30 hari sebelumnya.
     */
    private function card(string $label, Builder $query): array
    {
        $now = Carbon::now();
        $currentStart = $now->copy()->subDays(self::DELTA_DAYS);
        $previousStart = $now->copy()->subDays(self::DELTA_DAYS * 2);

        $current = (clone $query)->whereBetween('created_at', [$currentStart, $now])->count();
        $previous = (clone $query)->whereBetween('created_at', [$previousStart, $currentStart])->count();

        return [
            'label' => $label,
            'total' => (clone $query)->count(),
            'current' => $current,
            // null saat tidak ada pembanding — view menampilkan "+N baru" alih-alih persentase
            'percent' => $previous > 0 ? (int) round((($current - $previous) / $previous) * 100) : null,
        ];
    }

    /**
     * Feed "apa yang terjadi" — konten baru dihitung terlepas dari status moderasinya.
     */
    private function activity(int $limit = 10): Collection
    {
        $items = collect()
            ->concat(User::latest()->limit($limit)->get()
                ->map(fn (User $u) => $this->item('pengguna', 'Pengguna baru', $u->name, null, $u->created_at, null)))
            ->concat(Assembly::with('contributor')->latest()->limit($limit)->get()
                ->map(fn (Assembly $a) => $this->item('majelis', 'Majelis baru', $a->nama_majelis, $a->contributor?->name, $a->created_at, route('majelis.index'))))
            ->concat(Teacher::with('contributor')->latest()->limit($limit)->get()
                ->map(fn (Teacher $t) => $this->item('guru', 'Guru baru', $t->name, $t->contributor?->name, $t->created_at, route('guru.index'))))
            ->concat(Schedule::with('contributor')->latest()->limit($limit)->get()
                ->map(fn (Schedule $s) => $this->item('jadwal', 'Jadwal baru', $s->nama_jadwal, $s->contributor?->name, $s->created_at, route('jadwal-majelis.index'))))
            ->concat(Event::with('contributor')->latest()->limit($limit)->get()
                ->map(fn (Event $e) => $this->item('acara', 'Acara baru', $e->name, $e->contributor?->name, $e->created_at, route('event.index'))))
            ->concat(Wirid::with('contributor')->latest()->limit($limit)->get()
                ->map(fn (Wirid $w) => $this->item('amalan', 'Amalan baru', $w->nama, $w->contributor?->name, $w->created_at, route('wirid.index'))))
            ->concat(Post::with('user')->latest()->limit($limit)->get()
                ->map(fn (Post $p) => $this->item('tulisan', 'Tulisan baru', $p->title, $p->user?->name, $p->created_at, route('posts.index'))))
            ->concat($this->recentComments($limit));

        return $this->newest($items, $limit);
    }

    private function recentComments(int $limit): Collection
    {
        return Comment::with(['user', 'commentable'])->latest()->limit($limit)->get()
            // commentable bisa null kalau entitas induknya sudah dihapus — item itu dibuang
            ->filter(fn (Comment $c) => $c->commentable !== null)
            ->map(fn (Comment $c) => $this->item(
                'komentar',
                'Komentar baru',
                $this->labelOf($c->commentable),
                $c->user?->name,
                $c->created_at,
                null,
            ));
    }

    private function item(string $type, string $label, ?string $title, ?string $actor, ?Carbon $createdAt, ?string $url): array
    {
        return [
            'type' => $type,
            'label' => $label,
            'title' => $title ?: '(tanpa judul)',
            'actor' => $actor,
            'url' => $url,
            'created_at' => $createdAt,
        ];
    }

    /**
     * Tiap model memakai nama kolom judul yang berbeda — samakan seperti
     * ModerasiController@getLabel().
     */
    private function labelOf($entity): ?string
    {
        return $entity->nama_majelis
            ?? $entity->name
            ?? $entity->nama_jadwal
            ?? $entity->nama
            ?? $entity->title
            ?? null;
    }

    private function newest(Collection $items, int $limit): Collection
    {
        return $items
            ->filter(fn (array $item) => $item['created_at'] !== null)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();
    }

    private function pendingAssemblies(): Builder
    {
        return Assembly::where('contribution_status', 'pending');
    }

    private function pendingTeachers(): Builder
    {
        return Teacher::where('contribution_status', 'pending');
    }

    private function pendingSchedules(): Builder
    {
        return Schedule::where('contribution_status', 'pending');
    }

    private function pendingWirids(): Builder
    {
        return Wirid::where('contribution_status', 'pending');
    }

    /**
     * Acara buatan admin (user_id null) tidak pernah masuk antrian moderasi —
     * konsisten dengan Livewire\Acara.
     */
    private function pendingEvents(): Builder
    {
        return Event::where('status', 'pending')->whereNotNull('user_id');
    }

    private function pendingScheduleNotes(): Builder
    {
        return ScheduleNote::where('contribution_status', 'pending');
    }
}
