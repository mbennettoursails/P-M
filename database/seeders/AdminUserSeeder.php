<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin user.
     */
    public function run(): void
    {
        // Create default admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin123!Change'),
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info('✅ Admin user created successfully!');
        $this->command->warn('⚠️  Default credentials:');
        $this->command->line('   Email: admin@example.com');
        $this->command->line('   Password: Admin123!Change');
        $this->command->error('🔒 CHANGE THIS PASSWORD IMMEDIATELY IN PRODUCTION!');
    }
}
