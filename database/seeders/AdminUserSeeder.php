<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding admin user');

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@email.com',
            'password' => bcrypt('admin!'),
            'role' => 'superadmin',
            'is_verified' => 1,
        ]);

        $this->command->info('Admin user seeded successfully');
    }
}
