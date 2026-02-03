<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Run permission seeder first
        $this->call(PermissionSeeder::class);

        // Create default super admin user
        $user = User::create([
            'username' => 'admin',
            'email' => 'admin@eduplex.com',
            'password' => Hash::make('password'),
            'full_name' => 'System Administrator',
            'phone' => '+1234567890',
            'user_type' => 'admin',
            'status' => 'active',
        ]);

        // Assign Super Admin role
        $user->assignRole('Super Admin');

        Admin::create([
            'user_id' => $user->id,
            'department' => 'Administration',
        ]);

        // Run sample data seeder for testing
        $this->call(SampleDataSeeder::class);
    }
}
