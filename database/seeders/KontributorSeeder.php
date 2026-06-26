<?php

namespace Database\Seeders;

use App\Models\KontribusiXpSetting;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class KontributorSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'Kontributor']);

        $defaults = [
            ['contribution_type' => 'majelis', 'points' => 50, 'label' => 'Majelis Baru'],
            ['contribution_type' => 'guru', 'points' => 40, 'label' => 'Guru Baru'],
            ['contribution_type' => 'jadwal', 'points' => 15, 'label' => 'Jadwal Majelis'],
            ['contribution_type' => 'acara', 'points' => 25, 'label' => 'Acara / Event'],
            ['contribution_type' => 'amalan', 'points' => 30, 'label' => 'Amalan / Wirid'],
            ['contribution_type' => 'catatan_pengajian', 'points' => 10, 'label' => 'Catatan Pengajian'],
        ];

        foreach ($defaults as $row) {
            KontribusiXpSetting::firstOrCreate(
                ['contribution_type' => $row['contribution_type']],
                ['points' => $row['points'], 'label' => $row['label']]
            );
        }
    }
}
