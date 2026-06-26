<?php

namespace App\Services;

use App\Models\Contribution;
use App\Models\KontribusiXpSetting;
use App\Models\User;
use App\Notifications\BadgeNaik;
use App\Notifications\KontribusiDisetujui;
use App\Notifications\KontribusiDitolak;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KhidmahService
{
    public function approve(Model $entity, string $contributionType, string $entityLabel): void
    {
        DB::transaction(function () use ($entity, $contributionType, $entityLabel) {
            if ($entity->contribution_status === 'approved') {
                return;
            }

            $userId = $this->getContributorId($entity);
            if (! $userId) {
                return;
            }

            $points = KontribusiXpSetting::pointsFor($contributionType);

            $entity->contribution_status = 'approved';
            $entity->moderated_at = now();
            $entity->rejection_reason = null;
            $entity->save();

            Contribution::where('contributable_id', $entity->id)
                ->where('contributable_type', get_class($entity))
                ->where('user_id', $userId)
                ->update(['points_earned' => $points]);

            $user = User::find($userId);
            if (! $user) {
                return;
            }

            $oldBadge = $user->badge_title;
            $user->increment('total_khidmah_points', $points);
            $user->refresh();
            $badgeChanged = $user->updateBadge();

            $user->notify(new KontribusiDisetujui($entityLabel, $points));

            if ($badgeChanged && $user->badge_title !== $oldBadge) {
                $user->notify(new BadgeNaik($user->badge_title));
            }
        });
    }

    public function reject(Model $entity, string $entityLabel, string $reason): void
    {
        $entity->contribution_status = 'rejected';
        $entity->rejection_reason = $reason;
        $entity->moderated_at = now();
        $entity->save();

        $userId = $this->getContributorId($entity);
        if (! $userId) {
            return;
        }

        $user = User::find($userId);
        $user?->notify(new KontribusiDitolak($entityLabel, $reason));
    }

    public function revoke(Model $entity, string $contributionType, string $entityLabel, string $reason): void
    {
        DB::transaction(function () use ($entity, $contributionType, $entityLabel, $reason) {
            if ($entity->contribution_status !== 'approved') {
                return;
            }

            $userId = $this->getContributorId($entity);
            if (! $userId) {
                return;
            }

            $contribution = Contribution::where('contributable_id', $entity->id)
                ->where('contributable_type', get_class($entity))
                ->where('user_id', $userId)
                ->first();

            $pointsToDeduct = $contribution?->points_earned ?? 0;

            $entity->contribution_status = 'rejected';
            $entity->rejection_reason = $reason;
            $entity->moderated_at = now();
            $entity->save();

            if ($contribution) {
                $contribution->points_earned = 0;
                $contribution->save();
            }

            $user = User::find($userId);
            if (! $user) {
                return;
            }

            if ($pointsToDeduct > 0) {
                $newPoints = max(0, $user->total_khidmah_points - $pointsToDeduct);
                $user->total_khidmah_points = $newPoints;
                $user->save();
                $user->updateBadge();
            }

            $user->notify(new KontribusiDitolak($entityLabel, $reason));
        });
    }

    private function getContributorId(Model $entity): ?int
    {
        return $entity->contributor_user_id ?? $entity->user_id ?? null;
    }
}
