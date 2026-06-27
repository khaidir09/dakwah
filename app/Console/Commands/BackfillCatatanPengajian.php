<?php

namespace App\Console\Commands;

use App\Models\Contribution;
use App\Models\KontribusiXpSetting;
use App\Models\ScheduleNote;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillCatatanPengajian extends Command
{
    protected $signature = 'app:backfill-catatan-pengajian
                            {--dry-run : Tampilkan yang akan diproses tanpa mengubah database}';

    protected $description = 'Backfill contribution_status, moderated_at, contributions, dan poin XP untuk catatan pengajian publik lama yang sudah disetujui admin';

    public function handle(): int
    {
        $notes = ScheduleNote::where('visibility', 'Public')
            ->where('status', 'Approved')
            ->whereNull('contribution_status')
            ->with('user')
            ->get();

        if ($notes->isEmpty()) {
            $this->info('Tidak ada catatan pengajian yang perlu di-backfill.');

            return self::SUCCESS;
        }

        $pointsPerNote = KontribusiXpSetting::pointsFor('catatan_pengajian');

        $this->info("Ditemukan {$notes->count()} catatan pengajian (poin per catatan: {$pointsPerNote} XP).");

        if ($this->option('dry-run')) {
            $this->table(
                ['ID', 'User', 'Schedule ID', 'Dibuat'],
                $notes->map(fn ($n) => [
                    $n->id,
                    $n->user?->name ?? "user #{$n->user_id}",
                    $n->schedule_id,
                    $n->created_at?->format('Y-m-d'),
                ])->all()
            );
            $this->warn('[dry-run] Tidak ada perubahan yang dilakukan.');

            return self::SUCCESS;
        }

        $processed = 0;
        $bar = $this->output->createProgressBar($notes->count());
        $bar->start();

        DB::transaction(function () use ($notes, $pointsPerNote, &$processed, $bar) {
            $pointsPerUser = [];

            foreach ($notes as $note) {
                $userId = $note->user_id;

                if (! $userId) {
                    $bar->advance();
                    continue;
                }

                // Buat atau perbarui record contribution
                Contribution::updateOrCreate(
                    [
                        'contributable_id' => $note->id,
                        'contributable_type' => ScheduleNote::class,
                        'user_id' => $userId,
                    ],
                    ['points_earned' => $pointsPerNote]
                );

                // Tandai catatan sebagai approved dan catat waktu moderasi
                $note->contribution_status = 'approved';
                $note->moderated_at = $note->updated_at ?? now();
                $note->saveQuietly();

                $pointsPerUser[$userId] = ($pointsPerUser[$userId] ?? 0) + $pointsPerNote;
                $processed++;
                $bar->advance();
            }

            // Perbarui total_khidmah_points dan badge sekali per user
            foreach ($pointsPerUser as $userId => $totalPoints) {
                $user = User::find($userId);
                if (! $user) {
                    continue;
                }

                $user->increment('total_khidmah_points', $totalPoints);
                $user->refresh();
                $user->updateBadge();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Selesai. {$processed} catatan pengajian berhasil diproses.");

        return self::SUCCESS;
    }
}
