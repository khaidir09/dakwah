<?php

namespace App\Console\Commands;

use App\Models\ApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiClientCreate extends Command
{
    protected $signature = 'api:client-create {name : Nama partner} {--limit=60 : Batas request per menit}';

    protected $description = 'Membuat API key baru untuk konsumen API publik';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        if ($limit < 1 || $limit > 10000) {
            $this->error('Batas request per menit harus antara 1 dan 10000.');

            return self::FAILURE;
        }

        do {
            $key = Str::random(ApiClient::KEY_LENGTH);
            $prefix = substr($key, 0, ApiClient::PREFIX_LENGTH);
        } while (ApiClient::where('key_prefix', $prefix)->exists());

        $client = ApiClient::create([
            'name' => $this->argument('name'),
            'key_prefix' => $prefix,
            'key_hash' => Hash::make($key),
            'rate_limit_per_minute' => $limit,
        ]);

        $this->newLine();
        $this->info('API client berhasil dibuat.');
        $this->table(['Field', 'Nilai'], [
            ['Nama', $client->name],
            ['Prefix', $client->key_prefix],
            ['Limit/menit', $client->rate_limit_per_minute],
        ]);
        $this->newLine();
        $this->line('API key (kirim lewat header X-API-Key):');
        $this->line("  <fg=yellow>{$key}</>");
        $this->newLine();
        $this->warn('Key ini hanya ditampilkan sekali dan tidak dapat ditampilkan ulang. Simpan sekarang.');

        return self::SUCCESS;
    }
}
