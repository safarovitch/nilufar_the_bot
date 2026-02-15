<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists to avoid duplicates
        if (!User::where('email', 'admin@nilufar.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@nilufar.com',
                'password' => Hash::make('password'), // Change this in production!
                'email_verified_at' => now(),
            ]);
            $this->command->info('Admin user created: admin@nilufar.com / password');
        } else {
            $this->command->info('Admin user already exists.');
        }
    }
}
