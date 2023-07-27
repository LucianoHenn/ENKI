<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $allPermissions = Permission::all();

        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
            ],
            [
                'name' => 'normal',
                'display_name' => 'Normal',
            ],
        ];

        foreach ($roles as $index => $role) {
            $newRole = Role::updateOrCreate([
                'name' => $role['name'],
            ], [
                'display_name' => $role['display_name'],
                'order' => $role['order'] ?? $index,
            ]);

            if ($newRole->name === 'normal') {
                $permissions = Permission::where('name', 'NOT LIKE', '%-roles')
                                         ->where('name', 'NOT LIKE', '%-users')
                                         ->get();
            } else {
                $permissions = $allPermissions;
            }

            $newRole->syncPermissions($permissions);
        }

    }
}
