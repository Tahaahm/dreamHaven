<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin Account
        Admin::create([
            'username' => 'superadmin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('DreamHaven@2026!SuperAdmin#Secure'),
            'phone' => '07517812988',
            'role' => 'super_admin',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Super Admin created successfully!');
        $this->command->info('📧 Email: admin@gmail.com');
        $this->command->info('📱 Phone: 07517812988');
        $this->command->info('🔑 Password: DreamHaven@2026!SuperAdmin#Secure');
    }
}
