<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class BackfillKontributorUsername extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-kontributor-username';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Isi username & kontributor_since untuk kontributor lama yang belum memilikinya';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::role('Kontributor')->get();
        $updated = 0;

        foreach ($users as $user) {
            $changed = false;

            if (empty($user->username)) {
                $user->username = $user->generateUniqueUsername();
                $changed = true;
            }

            if (empty($user->kontributor_since)) {
                $user->kontributor_since = $user->created_at;
                $changed = true;
            }

            if ($changed) {
                $user->save();
                $updated++;
            }
        }

        $this->info("Backfill selesai. {$updated} dari {$users->count()} kontributor diperbarui.");

        return self::SUCCESS;
    }
}
