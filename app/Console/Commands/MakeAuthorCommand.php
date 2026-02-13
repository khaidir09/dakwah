<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class MakeAuthorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-author {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign the Penulis role to a user by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        $user->assignRole('Penulis');
        $this->info("User {$user->name} ({$email}) has been assigned the 'Penulis' role.");
        return 0;
    }
}
