<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder for creating admin user
 */
class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@simtek.com',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
            'phone' => null,
            'address' => null,
            'device_token' => null,
        ]);
    }
}
