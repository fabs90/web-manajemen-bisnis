<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::factory()->create([
            "name" => "Admin",
            "email" => "admin@example.com",
            "password" => bcrypt("password"),
            "role" => "superadmin",
            "is_verified" => true,
        ]);

        // UKM
        User::factory()->create([
            "name" => "umkmnusantara",
            "email" => "umkmnusantara@example.com",
            "password" => bcrypt("umkmnusantara"),
            "role" => "ukm",
            "is_verified" => true,
        ]);
    }
}
