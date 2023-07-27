<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            ['name' => 'view-users'],
            ['name' => 'create-users'],
            ['name' => 'update-users'],
            ['name' => 'delete-users'],

            ['name' => 'view-roles'],
            ['name' => 'create-roles'],
            ['name' => 'update-roles'],
            ['name' => 'delete-roles'],

            ['name' => 'view-clients'],
            ['name' => 'create-clients'],
            ['name' => 'update-clients'],
            ['name' => 'delete-clients'],
        ];

        foreach ($permissions as $index => $permission) {
            Permission::updateOrCreate(['name' => $permission['name']], [
                'name' => $permission['name'],
                'order' => $permission['order'] ?? $index,
                'display_name' => $permission['display_name']
                                  ?? Str::ucfirst(Str::replace('-', ' ', $permission['name'])),
            ]);
        }
    }
}
