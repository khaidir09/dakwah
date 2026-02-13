<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PostRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage posts',
            'create posts',
            'edit own posts',
            'delete own posts',
            'edit all posts',
            'delete all posts',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create 'Penulis' role and assign permissions
        $role = Role::firstOrCreate(['name' => 'Penulis']);
        $role->syncPermissions(['manage posts', 'create posts', 'edit own posts', 'delete own posts']);

        // Ensure 'Super Admin' exists (just in case) and give all permissions
        $adminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $adminRole->givePermissionTo(Permission::all());
    }
}
