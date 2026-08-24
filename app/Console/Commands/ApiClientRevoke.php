<?php

namespace App\Console\Commands;

use App\Models\ApiClient;
use Illuminate\Console\Command;

class ApiClientRevoke extends Command
{
    protected $signature = 'api:client-revoke {prefix : Prefix key yang akan dicabut}';

    protected $description = 'Mencabut API key berdasarkan prefix-nya';

    public function handle(): int
    {
        $client = ApiClient::where('key_prefix', $this->argument('prefix'))->first();

        if (! $client) {
            $this->error('API client dengan prefix tersebut tidak ditemukan.');

            return self::FAILURE;
        }

        if ($client->revoked_at) {
            $this->warn("API client \"{$client->name}\" sudah dicabut pada {$client->revoked_at}.");

            return self::SUCCESS;
        }

        $client->update(['revoked_at' => now()]);

        $this->info("API client \"{$client->name}\" berhasil dicabut.");

        return self::SUCCESS;
    }
}
