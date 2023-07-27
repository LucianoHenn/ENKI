<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (User::count() === 0) {
            $user = User::create([
                'name' => 'Radovan',
                'email' => 'radovan@mforward.it',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);

            $user->assignRole('admin');
        }

        if (app()->environment('local')) {
            User::factory()->count(100)->create();
        }

    }
}
