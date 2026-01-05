<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class GenerateMajelisLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'majelis:invite {minutes=1440}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a signed URL for Majelis Onboarding';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = $this->argument('minutes');
        $url = URL::temporarySignedRoute(
            'majelis.onboarding',
            now()->addMinutes((int)$minutes)
        );

        $this->info("Link Registrasi Majelis (Valid untuk {$minutes} menit):");
        $this->line($url);

        return Command::SUCCESS;
    }
}
